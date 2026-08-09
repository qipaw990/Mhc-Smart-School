<?php

namespace App\Http\Controllers;

use App\Models\LearningObjective;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbtQuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $query = QuestionBank::with(['subject', 'teacher', 'learningObjective'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc');

        if ($isGuru && $teacher) {
            $query->where('teacher_id', $teacher->id);
        }

        $banks = $query->paginate(12);

        $activeScheduleId = \App\Models\Schedule::where('status', 'active')->value('id');

        if ($isGuru && $teacher) {
            $mySlots = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with('subject')
                ->get();
            $subjects = $mySlots->pluck('subject')->filter()->unique('id')->sortBy('name')->values();
            $teachers = collect([$teacher]);
        } else {
            $subjects = Subject::where('status', 'active')->get();
            $teachers = Teacher::all();
        }

        return view('cbt.banks.index', compact('banks', 'subjects', 'teachers', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'title' => 'required|string|max:255',
            'phase' => 'required|in:E,F',
            'description' => 'nullable|string',
        ]);

        $school = School::first();

        $bank = QuestionBank::create(array_merge($validated, [
            'school_id' => $school->id,
        ]));

        return redirect()->route('cbt.banks.show', $bank->id)
            ->with('success', 'Bank Soal berhasil dibuat! Silakan tambahkan butir-butir soal.');
    }

    public function show(QuestionBank $bank)
    {
        $bank->load(['subject', 'teacher', 'learningObjective', 'questions.options']);
        $learningObjectives = LearningObjective::where('status', 'active')->get();

        return view('cbt.banks.show', compact('bank', 'learningObjectives'));
    }

    public function storeQuestion(Request $request, QuestionBank $bank)
    {
        $validated = $request->validate([
            'type' => 'required|in:pg,pgk,true_false,matching,short_answer,essay',
            'cognitive_level' => 'required|in:lots,mots,hots',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_text' => 'required|string',
            'code_snippet' => 'nullable|string',
            'score_weight' => 'required|numeric|min:1|max:100',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',
            'correct_option' => 'nullable|string',
            'matching_pairs' => 'nullable|array',
        ]);

        $orderNum = $bank->questions()->count() + 1;

        $question = Question::create([
            'question_bank_id' => $bank->id,
            'type' => $validated['type'],
            'cognitive_level' => $validated['cognitive_level'],
            'difficulty' => $validated['difficulty'],
            'question_text' => $validated['question_text'],
            'code_snippet' => $validated['code_snippet'] ?? null,
            'score_weight' => $validated['score_weight'],
            'order_number' => $orderNum,
            'explanation' => $validated['explanation'] ?? null,
        ]);

        // Save Options based on type
        if ($validated['type'] === 'pg' && !empty($validated['options'])) {
            foreach ($validated['options'] as $label => $text) {
                if (trim($text) !== '') {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_label' => $label,
                        'option_text' => $text,
                        'is_correct' => ($label === $request->input('correct_option')),
                    ]);
                }
            }
        } elseif ($validated['type'] === 'pgk' && !empty($validated['options'])) {
            $correctOptions = $request->input('correct_options', []);
            foreach ($validated['options'] as $label => $text) {
                if (trim($text) !== '') {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_label' => $label,
                        'option_text' => $text,
                        'is_correct' => in_array($label, $correctOptions),
                    ]);
                }
            }
        } elseif ($validated['type'] === 'true_false') {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'A',
                'option_text' => 'BENAR (True)',
                'is_correct' => ($request->input('tf_answer') === 'true'),
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'B',
                'option_text' => 'SALAH (False)',
                'is_correct' => ($request->input('tf_answer') === 'false'),
            ]);
        } elseif ($validated['type'] === 'matching' && !empty($validated['matching_pairs'])) {
            foreach ($validated['matching_pairs'] as $pair) {
                if (!empty($pair['left']) && !empty($pair['right'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $pair['left'],
                        'matching_pair' => $pair['right'],
                        'is_correct' => true,
                    ]);
                }
            }
        }

        return back()->with('success', 'Butir soal baru berhasil ditambahkan ke Bank Soal!');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $validated = $request->validate([
            'type' => 'required|in:pg,pgk,true_false,matching,short_answer,essay',
            'cognitive_level' => 'required|in:lots,mots,hots',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_text' => 'required|string',
            'code_snippet' => 'nullable|string',
            'score_weight' => 'required|numeric|min:1|max:100',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',
            'correct_option' => 'nullable|string',
            'correct_options' => 'nullable|array',
            'matching_pairs' => 'nullable|array',
            'tf_answer' => 'nullable|string',
        ]);

        $question->update([
            'type' => $validated['type'],
            'cognitive_level' => $validated['cognitive_level'],
            'difficulty' => $validated['difficulty'],
            'question_text' => $validated['question_text'],
            'code_snippet' => $validated['code_snippet'] ?? null,
            'score_weight' => $validated['score_weight'],
            'explanation' => $validated['explanation'] ?? null,
        ]);

        // Delete old options and recreate
        $question->options()->delete();

        // Save Options based on type
        if ($validated['type'] === 'pg' && !empty($validated['options'])) {
            foreach ($validated['options'] as $label => $text) {
                if (trim($text) !== '') {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_label' => $label,
                        'option_text' => $text,
                        'is_correct' => ($label === $request->input('correct_option')),
                    ]);
                }
            }
        } elseif ($validated['type'] === 'pgk' && !empty($validated['options'])) {
            $correctOptions = $request->input('correct_options', []);
            foreach ($validated['options'] as $label => $text) {
                if (trim($text) !== '') {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_label' => $label,
                        'option_text' => $text,
                        'is_correct' => in_array($label, $correctOptions),
                    ]);
                }
            }
        } elseif ($validated['type'] === 'true_false') {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'A',
                'option_text' => 'BENAR (True)',
                'is_correct' => ($request->input('tf_answer') === 'true'),
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'B',
                'option_text' => 'SALAH (False)',
                'is_correct' => ($request->input('tf_answer') === 'false'),
            ]);
        } elseif ($validated['type'] === 'matching' && !empty($validated['matching_pairs'])) {
            foreach ($validated['matching_pairs'] as $pair) {
                if (!empty($pair['left']) && !empty($pair['right'])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $pair['left'],
                        'matching_pair' => $pair['right'],
                        'is_correct' => true,
                    ]);
                }
            }
        }

        return back()->with('success', 'Butir soal nomor ' . $question->order_number . ' berhasil diperbarui!');
    }

    public function destroyQuestion(Question $question)
    {
        $question->delete();
        return back()->with('success', 'Butir soal berhasil dihapus.');
    }

    public function destroy(QuestionBank $bank)
    {
        $bank->delete();
        return redirect()->route('cbt.banks.index')->with('success', 'Bank Soal berhasil dihapus.');
    }
}
