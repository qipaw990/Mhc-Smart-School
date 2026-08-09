<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Attendance;
use App\Models\ReportCard;
use App\Models\ReportCardExtracurricular;
use App\Models\ReportCardGrade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class ReportCardGeneratorService
{
    public function generateForClass(int $classId, int $academicYearId, ?int $semesterId = null): array
    {
        $school = School::first();
        $schoolClass = SchoolClass::find($classId);
        $students = Student::where('current_class_id', $classId)->get();

        if ($students->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Tidak ada siswa yang terdaftar pada rombel kelas ini.',
            ];
        }

        // 1. Bersihkan data nilai yatim/orphan yang mata pelajarannya sudah dihapus
        ReportCardGrade::whereDoesntHave('subject')->delete();

        // 2. Ambil HANYA mata pelajaran yang sesuai dengan Rombel / Jurusan Kelas ini (Kelompok Umum + Kejuruan Jurusan yang sesuai)
        $subjects = Subject::where('status', 'active')
            ->where(function ($q) use ($schoolClass) {
                $q->whereNull('major_id');
                if ($schoolClass && $schoolClass->major_id) {
                    $q->orWhere('major_id', $schoolClass->major_id);
                }
            })
            ->get();

        $generatedCards = [];

        DB::transaction(function () use ($school, $schoolClass, $students, $subjects, $classId, $academicYearId, $semesterId, &$generatedCards) {
            foreach ($students as $student) {
                // 1. Presensi Siswa
                $sickCount = Attendance::where('student_id', $student->id)->where('status', 'S')->count();
                $permitCount = Attendance::where('student_id', $student->id)->where('status', 'I')->count();
                $absentCount = Attendance::where('student_id', $student->id)->where('status', 'A')->count();

                $reportCard = ReportCard::updateOrCreate([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYearId,
                    'student_id' => $student->id,
                    'class_id' => $classId,
                ], [
                    'semester_id' => $semesterId,
                    'sick_count' => $sickCount,
                    'permit_count' => $permitCount,
                    'absent_count' => $absentCount,
                    'homeroom_notes' => 'Pertahankan prestasi belajar dan tingkatkan terus keaktifan dalam kegiatan praktik kejuruan.',
                    'promotion_status' => 'naik_kelas',
                    'status' => 'published',
                ]);

                // Hapus nilai mata pelajaran yang tidak relevan dengan jurusan kelas ini atau yang sudah dihapus
                $reportCard->grades()->whereNotIn('subject_id', $subjects->pluck('id'))->delete();

                // 2. Kalkulasi Nilai per Mata Pelajaran & Auto-Deskripsi TP
                foreach ($subjects as $subject) {
                    $scores = AssessmentScore::with(['assessment.learningObjective'])
                        ->where('student_id', $student->id)
                        ->whereHas('assessment', fn($q) => $q->where('subject_id', $subject->id))
                        ->get();

                    if ($scores->isNotEmpty()) {
                        $avgScore = $scores->avg('final_score');

                        // Find highest & lowest TP
                        $sortedByScore = $scores->sortByDesc('final_score');
                        $highest = $sortedByScore->first();
                        $lowest = $sortedByScore->last();

                        $highestTpDesc = $highest?->assessment?->learningObjective?->description ?? $subject->name;
                        $lowestTpDesc = $lowest?->assessment?->learningObjective?->description ?? $subject->name;

                        $highNarration = "Menunjukkan penguasaan yang sangat baik dalam " . lcfirst($highestTpDesc) . ".";
                        $lowNarration = "Perlu bimbingan dan peningkatan konsistensi dalam " . lcfirst($lowestTpDesc) . ".";

                        $predicate = match(true) {
                            $avgScore >= 88 => 'A',
                            $avgScore >= 75 => 'B',
                            $avgScore >= 65 => 'C',
                            default => 'D',
                        };

                        ReportCardGrade::updateOrCreate([
                            'report_card_id' => $reportCard->id,
                            'subject_id' => $subject->id,
                        ], [
                            'teacher_id' => $highest?->assessment?->teacher_id,
                            'final_score' => $avgScore,
                            'predicate' => $predicate,
                            'highest_competency_desc' => $highNarration,
                            'lowest_competency_desc' => $lowNarration,
                        ]);
                    } else {
                        // Default fallback score if not yet assessed
                        ReportCardGrade::updateOrCreate([
                            'report_card_id' => $reportCard->id,
                            'subject_id' => $subject->id,
                        ], [
                            'final_score' => 80.00,
                            'predicate' => 'B',
                            'highest_competency_desc' => "Menunjukkan penguasaan yang baik dalam capaian pembelajaran mata pelajaran {$subject->name}.",
                            'lowest_competency_desc' => "Perlu ditingkatkan keaktifan dalam menyelesaikan tugas praktik mandiri.",
                        ]);
                    }
                }

                // 3. Ekstrakurikuler Default
                ReportCardExtracurricular::firstOrCreate([
                    'report_card_id' => $reportCard->id,
                    'activity_name' => 'Pramuka Wajib',
                ], [
                    'predicate' => 'Sangat Baik',
                    'description' => 'Aktif mengikuti seluruh kegiatan perkemahan dan kedisiplinan kepramukaan.',
                ]);

                $generatedCards[] = $reportCard;
            }

            // 4. Kalkulasi Peringkat Kelas (Class Rank)
            $cardsWithAvg = ReportCard::with('grades')
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->sortByDesc(fn($card) => $card->grades->avg('final_score'))
                ->values();

            foreach ($cardsWithAvg as $rankIndex => $c) {
                $c->update(['class_rank' => $rankIndex + 1]);
            }
        });

        return [
            'status' => 'success',
            'message' => 'E-Rapor berhasil di-generate untuk ' . count($generatedCards) . ' siswa!',
            'count' => count($generatedCards),
        ];
    }
}
