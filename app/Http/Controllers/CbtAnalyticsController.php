<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\StudentExamAnswer;
use Illuminate\Http\Request;

class CbtAnalyticsController extends Controller
{
    public function index(Exam $exam)
    {
        $exam->load(['questionBank.questions', 'subject', 'teacher']);
        $studentExams = StudentExam::with(['student.currentClass'])
            ->where('exam_id', $exam->id)
            ->get();

        // Exam Summary Analytics
        $totalStudents = $studentExams->count();
        $avgScore = $studentExams->avg('total_score') ?? 0;
        $maxScore = $studentExams->max('total_score') ?? 0;
        $minScore = $studentExams->min('total_score') ?? 0;
        $passedCount = $studentExams->where('is_passed', true)->count();
        $failedCount = $totalStudents - $passedCount;

        // Item Analysis (Analisis Butir Soal: Tingkat Kesukaran & Daya Pembeda)
        $questions = $exam->questionBank->questions;
        $itemAnalytics = [];

        foreach ($questions as $q) {
            $answers = StudentExamAnswer::whereHas('studentExam', fn($sq) => $sq->where('exam_id', $exam->id))
                ->where('question_id', $q->id)
                ->get();

            $totalAnswered = $answers->count();
            $correctCount = $answers->where('is_correct', true)->count();
            $difficultyIndex = $totalAnswered > 0 ? round($correctCount / $totalAnswered, 2) : 0;

            $difficultyCategory = match(true) {
                $difficultyIndex >= 0.70 => 'Mudah',
                $difficultyIndex >= 0.30 => 'Sedang',
                default => 'Sukar',
            };

            $itemAnalytics[] = [
                'question' => $q,
                'total_answered' => $totalAnswered,
                'correct_count' => $correctCount,
                'difficulty_index' => $difficultyIndex,
                'difficulty_category' => $difficultyCategory,
            ];
        }

        return view('cbt.analytics.index', compact(
            'exam',
            'studentExams',
            'totalStudents',
            'avgScore',
            'maxScore',
            'minScore',
            'passedCount',
            'failedCount',
            'itemAnalytics'
        ));
    }

    public function studentDetail(StudentExam $studentExam)
    {
        $studentExam->load(['student.currentClass', 'exam.subject', 'answers.question.options']);

        return view('cbt.analytics.student_detail', compact('studentExam'));
    }

    public function gradeEssay(Request $request, StudentExamAnswer $answer)
    {
        $validated = $request->validate([
            'score_obtained' => 'required|numeric|min:0|max:' . $answer->question->score_weight,
            'teacher_notes' => 'nullable|string',
        ]);

        $answer->update([
            'score_obtained' => $validated['score_obtained'],
            'is_correct' => $validated['score_obtained'] > 0,
            'teacher_notes' => $validated['teacher_notes'],
        ]);

        // Recalculate total score
        $studentExam = $answer->studentExam;
        $newTotal = $studentExam->answers()->sum('score_obtained');
        $studentExam->update([
            'total_score' => $newTotal,
            'is_passed' => $newTotal >= $studentExam->exam->kktp_score,
        ]);

        return back()->with('success', 'Nilai Essay berhasil diperbarui!');
    }
}
