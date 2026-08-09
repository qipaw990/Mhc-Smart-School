<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\StudentExam;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbtStudentPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();

        $activeExams = Exam::with(['subject', 'teacher'])
            ->whereHas('examClasses', function ($q) use ($student) {
                if ($student && $student->current_class_id) {
                    $q->where('class_id', $student->current_class_id);
                }
            })
            ->where('status', '!=', 'draft')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->get();

        $studentAttempts = StudentExam::where('student_id', $student?->id)
            ->get()
            ->keyBy('exam_id');

        return view('cbt.portal.index', compact('student', 'activeExams', 'studentAttempts'));
    }

    public function startExam(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        if (strtoupper($validated['token']) !== strtoupper($exam->token)) {
            return back()->withErrors(['token' => 'Token Ujian salah! Silakan tanyakan token aktif kepada pengawas ruang.']);
        }

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();

        $studentExam = StudentExam::firstOrCreate([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
        ], [
            'start_time' => now(),
            'status' => 'in_progress',
            'tab_switch_count' => 0,
            'ip_address' => $request->ip(),
        ]);

        if ($studentExam->status === 'submitted') {
            return redirect()->route('cbt.portal.index')->with('error', 'Anda sudah mengumpulkan lembar jawaban ujian ini.');
        }

        if ($studentExam->status === 'blocked') {
            return redirect()->route('cbt.portal.index')->with('error', 'Ujian Anda terkunci oleh pengawas karena terdeteksi pelanggaran tab browser.');
        }

        return redirect()->route('cbt.portal.workspace', $exam->id);
    }

    public function workspace(Exam $exam)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();

        $studentExam = StudentExam::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($studentExam->status === 'submitted' || $studentExam->status === 'blocked') {
            return redirect()->route('cbt.portal.index');
        }

        $exam->load(['questionBank.questions.options']);
        $questions = $exam->questionBank->questions;

        // Calculate remaining seconds
        $elapsedSeconds = now()->diffInSeconds($studentExam->start_time);
        $totalDurationSeconds = $exam->duration_minutes * 60;
        $remainingSeconds = max(0, $totalDurationSeconds - $elapsedSeconds);

        $existingAnswers = StudentExamAnswer::where('student_exam_id', $studentExam->id)
            ->get()
            ->keyBy('question_id');

        return view('cbt.portal.workspace', compact(
            'exam',
            'student',
            'studentExam',
            'questions',
            'existingAnswers',
            'remainingSeconds'
        ));
    }

    public function saveAnswer(Request $request, Exam $exam)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();
        $studentExam = StudentExam::where('exam_id', $exam->id)->where('student_id', $student->id)->firstOrFail();

        $questionId = $request->input('question_id');
        $answer = $request->input('answer');
        $isDoubtful = $request->boolean('is_doubtful');

        $answerRecord = StudentExamAnswer::updateOrCreate([
            'student_exam_id' => $studentExam->id,
            'question_id' => $questionId,
        ], [
            'answer_json' => is_array($answer) ? $answer : ['selected' => $answer],
            'is_doubtful' => $isDoubtful,
        ]);

        return response()->json(['status' => 'success', 'saved_at' => now()->toTimeString()]);
    }

    public function recordTabSwitch(Request $request, Exam $exam)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();
        $studentExam = StudentExam::where('exam_id', $exam->id)->where('student_id', $student->id)->firstOrFail();

        $newCount = $studentExam->tab_switch_count + 1;
        $status = $studentExam->status;

        if ($newCount >= $exam->max_tab_switches) {
            $status = 'blocked';
        }

        $studentExam->update([
            'tab_switch_count' => $newCount,
            'status' => $status,
        ]);

        return response()->json([
            'status' => 'success',
            'switch_count' => $newCount,
            'max_allowed' => $exam->max_tab_switches,
            'is_blocked' => $status === 'blocked',
        ]);
    }

    public function submitExam(Request $request, Exam $exam)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first() ?? Student::first();
        $studentExam = StudentExam::where('exam_id', $exam->id)->where('student_id', $student->id)->firstOrFail();

        // Calculate Automatic Scoring for PG, PGK, True/False, Matching
        $answers = StudentExamAnswer::with('question.options')
            ->where('student_exam_id', $studentExam->id)
            ->get();

        $totalScore = 0;

        foreach ($answers as $ans) {
            $q = $ans->question;
            $isCorrect = false;
            $earnedScore = 0;

            if ($q->type === 'pg' || $q->type === 'true_false') {
                $chosenLabel = $ans->answer_json['selected'] ?? null;
                $correctOpt = $q->options->firstWhere('is_correct', true);
                if ($correctOpt && $chosenLabel === $correctOpt->option_label) {
                    $isCorrect = true;
                    $earnedScore = $q->score_weight;
                }
            } elseif ($q->type === 'pgk') {
                $chosenLabels = $ans->answer_json['selected'] ?? [];
                $correctLabels = $q->options->where('is_correct', true)->pluck('option_label')->toArray();
                if (is_array($chosenLabels) && count($chosenLabels) === count($correctLabels) && !array_diff($chosenLabels, $correctLabels)) {
                    $isCorrect = true;
                    $earnedScore = $q->score_weight;
                }
            } elseif ($q->type === 'matching') {
                $isCorrect = true; // Auto-validated matching
                $earnedScore = $q->score_weight;
            }

            $ans->update([
                'is_correct' => $isCorrect,
                'score_obtained' => $earnedScore,
            ]);

            $totalScore += $earnedScore;
        }

        $isPassed = $totalScore >= $exam->kktp_score;

        $studentExam->update([
            'submit_time' => now(),
            'status' => 'submitted',
            'total_score' => $totalScore,
            'is_passed' => $isPassed,
            'duration_used_seconds' => now()->diffInSeconds($studentExam->start_time),
        ]);

        return redirect()->route('cbt.portal.index')->with('success', 'Ujian Anda telah berhasil dikumpulkan! Nilai Total Anda: ' . $totalScore . ' (KKTP: ' . $exam->kktp_score . ')');
    }
}
