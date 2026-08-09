<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;

class CbtApiController extends Controller
{
    /**
     * List Published CBT Exams
     */
    public function exams(Request $request)
    {
        $user    = $request->user();
        $student = $user->student;

        $query = Exam::with(['subject', 'teacher'])
            ->where('status', 'published');

        $exams = $query->orderBy('start_time', 'desc')->get();

        // Attach student submission status
        if ($student) {
            $exams->transform(function ($exam) use ($student) {
                $session = StudentExam::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->first();

                $exam->student_session = $session ? [
                    'status'      => $session->status,
                    'score'       => $session->total_score,
                    'start_time'  => $session->start_time,
                    'submit_time' => $session->submit_time,
                ] : null;

                return $exam;
            });
        }

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * Get Workspace & Questions for an Exam
     */
    public function workspace(Request $request, Exam $exam)
    {
        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya akun siswa yang dapat mengerjakan ujian CBT.',
            ], 403);
        }

        // Get or start exam session
        $session = StudentExam::firstOrCreate([
            'exam_id'    => $exam->id,
            'student_id' => $student->id,
        ], [
            'status'     => 'in_progress',
            'start_time' => now(),
            'ip_address' => $request->ip(),
        ]);

        if ($session->status === 'submitted') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda telah menyelesaikan ujian ini.',
                'data'    => [
                    'total_score' => $session->total_score,
                    'submit_time' => $session->submit_time,
                ],
            ], 400);
        }

        // Fetch questions from question bank
        $questions = Question::with('options')
            ->where('question_bank_id', $exam->question_bank_id)
            ->get();

        // Fetch saved student answers
        $savedAnswers = StudentExamAnswer::where('student_exam_id', $session->id)
            ->pluck('selected_option_id', 'question_id')
            ->toArray();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'exam'          => $exam->load('subject'),
                'session'       => $session,
                'questions'     => $questions,
                'saved_answers' => $savedAnswers,
            ],
        ]);
    }

    /**
     * Auto Save Single Answer from Android App
     */
    public function saveAnswer(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'question_id'        => 'required|exists:questions,id',
            'selected_option_id' => 'required|exists:question_options,id',
        ]);

        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $session = StudentExam::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$session || $session->status === 'submitted') {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian telah selesai atau tidak aktif.'], 400);
        }

        StudentExamAnswer::updateOrCreate([
            'student_exam_id'    => $session->id,
            'question_id'        => $validated['question_id'],
        ], [
            'selected_option_id' => $validated['selected_option_id'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jawaban berhasil disimpan.',
        ]);
    }

    /**
     * Submit Final Exam & Auto Calculate Score
     */
    public function submit(Request $request, Exam $exam)
    {
        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $session = StudentExam::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$session) {
            return response()->json(['status' => 'error', 'message' => 'Sesi ujian tidak ditemukan.'], 404);
        }

        // Calculate score based on correct options
        $answers = StudentExamAnswer::with(['question.options', 'selectedOption'])
            ->where('student_exam_id', $session->id)
            ->get();

        $correctCount = 0;
        $totalQuestions = Question::where('question_bank_id', $exam->question_bank_id)->count();

        foreach ($answers as $ans) {
            if ($ans->selectedOption?->is_correct) {
                $correctCount++;
            }
        }

        $finalScore = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

        $session->update([
            'status'      => 'submitted',
            'submit_time' => now(),
            'total_score' => $finalScore,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Selamat, ujian berhasil diselesaikan!',
            'data'    => [
                'total_score'     => $finalScore,
                'correct_count'   => $correctCount,
                'total_questions' => $totalQuestions,
            ],
        ]);
    }
}
