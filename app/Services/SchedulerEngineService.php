<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleHistory;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\TeachingLoad;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SchedulerEngineService
{
    /**
     * Run automated constraint satisfaction scheduling algorithm.
     */
    public function generateSchedule(int $academicYearId, ?int $semesterId = null, string $scheduleName = 'Jadwal Otomatis', ?int $userId = null): array
    {
        $school = School::first();
        $loads = TeachingLoad::with(['teacher', 'subject', 'schoolClass', 'preferredRoom'])
            ->where('academic_year_id', $academicYearId)
            ->get();

        if ($loads->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Belum ada data Beban Mengajar (Teaching Loads). Silakan input pemetaan Guru & Rombel terlebih dahulu.',
            ];
        }

        $timeSlots = TimeSlot::where('is_break', false)
            ->whereIn('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
            ->orderBy('day')
            ->orderBy('period')
            ->get();

        $rooms = Room::where('status', 'active')->get();
        $availabilities = TeacherAvailability::where('is_available', false)->get()
            ->groupBy(fn($item) => $item->teacher_id . '_' . $item->day . '_' . $item->period);

        // Tracking state matrices for hard constraint enforcement
        $teacherOccupied = []; // [teacher_id][day][period] => true
        $classOccupied = [];   // [class_id][day][period] => true
        $roomOccupied = [];    // [room_id][day][period] => true

        $scheduledItems = [];
        $unplacedLoads = [];
        $penaltyScore = 0;

        // Group time slots by day for structured block allocation
        $slotsByDay = $timeSlots->groupBy('day');

        foreach ($loads as $load) {
            $hoursNeeded = $load->hours_per_week;
            $hoursPlaced = 0;

            // Determine block size (practical subjects: 2-4 JP blocks; theory: 2 JP)
            $isPractice = in_array($load->subject->type, ['practice', 'theory_practice']);
            $blockSize = $isPractice ? min(4, max(2, $hoursNeeded)) : min(2, $hoursNeeded);

            // Determine room (prefer designated lab/workshop, or class homebase)
            $targetRoom = $load->preferredRoom ?? $load->schoolClass->room ?? $rooms->first();

            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            // Shuffle or rotate days to achieve equal distribution
            shuffle($days);

            foreach ($days as $day) {
                if ($hoursPlaced >= $hoursNeeded) break;

                $daySlots = $slotsByDay->get($day, collect());
                $totalPeriods = $daySlots->count();

                // Search for continuous available block of periods
                for ($p = 0; $p <= $totalPeriods - $blockSize; $p++) {
                    if ($hoursPlaced >= $hoursNeeded) break;

                    $canPlace = true;
                    $candidateSlots = [];

                    for ($b = 0; $b < $blockSize; $b++) {
                        $slot = $daySlots[$p + $b];
                        $period = $slot->period;

                        // Check Hard Constraints
                        $isTeacherBusy = isset($teacherOccupied[$load->teacher_id][$day][$period]);
                        $isClassBusy = isset($classOccupied[$load->class_id][$day][$period]);
                        $isRoomBusy = $targetRoom ? isset($roomOccupied[$targetRoom->id][$day][$period]) : false;
                        $isTeacherUnavailable = isset($availabilities[$load->teacher_id . '_' . $day . '_' . $period]);

                        if ($isTeacherBusy || $isClassBusy || $isRoomBusy || $isTeacherUnavailable) {
                            $canPlace = false;
                            break;
                        }

                        $candidateSlots[] = $slot;
                    }

                    if ($canPlace) {
                        // Place block in matrix
                        foreach ($candidateSlots as $slot) {
                            $teacherOccupied[$load->teacher_id][$day][$slot->period] = true;
                            $classOccupied[$load->class_id][$day][$slot->period] = true;
                            if ($targetRoom) {
                                $roomOccupied[$targetRoom->id][$day][$slot->period] = true;
                            }

                            $scheduledItems[] = [
                                'time_slot_id' => $slot->id,
                                'teaching_load_id' => $load->id,
                                'teacher_id' => $load->teacher_id,
                                'class_id' => $load->class_id,
                                'subject_id' => $load->subject_id,
                                'room_id' => $targetRoom?->id,
                                'day' => $day,
                                'period' => $slot->period,
                                'consecutive_hours' => $blockSize,
                            ];
                        }
                        $hoursPlaced += $blockSize;
                        break; // Move to next day for load distribution
                    }
                }
            }

            if ($hoursPlaced < $hoursNeeded) {
                $unplacedLoads[] = [
                    'load' => $load,
                    'shortage' => $hoursNeeded - $hoursPlaced,
                ];
                $penaltyScore += ($hoursNeeded - $hoursPlaced) * 5;
            }
        }

        $optimizationScore = max(70.0, 100.0 - $penaltyScore);

        // Save generated schedule transaction
        $schedule = DB::transaction(function () use ($school, $academicYearId, $semesterId, $scheduleName, $userId, $optimizationScore, $scheduledItems) {
            $latestVersion = Schedule::where('academic_year_id', $academicYearId)->count() + 1;

            $schedule = Schedule::create([
                'school_id' => $school->id,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
                'name' => $scheduleName . ' v' . $latestVersion . '.0',
                'version' => $latestVersion . '.0',
                'status' => 'active',
                'optimization_score' => $optimizationScore,
                'created_by_user_id' => $userId ?? Auth::id(),
                'notes' => 'Generated automatically by MHC CSP Optimization Engine.',
            ]);

            // Deactivate older schedules in this academic year
            Schedule::where('academic_year_id', $academicYearId)
                ->where('id', '!=', $schedule->id)
                ->update(['status' => 'archived']);

            foreach ($scheduledItems as $item) {
                ScheduleItem::create(array_merge($item, ['schedule_id' => $schedule->id]));
            }

            ScheduleHistory::create([
                'schedule_id' => $schedule->id,
                'user_id' => $userId ?? Auth::id(),
                'action' => 'generated',
                'notes' => 'Schedule v' . $schedule->version . ' generated successfully with optimization score ' . $optimizationScore . '%.',
                'created_at' => now(),
            ]);

            return $schedule;
        });

        return [
            'status' => 'success',
            'schedule' => $schedule,
            'optimization_score' => $optimizationScore,
            'total_items' => count($scheduledItems),
            'unplaced_count' => count($unplacedLoads),
            'unplaced_loads' => $unplacedLoads,
        ];
    }

    /**
     * Real-time Conflict Detector & Explainability Analysis.
     */
    public function detectConflicts(int $scheduleId): array
    {
        $items = ScheduleItem::with(['teacher', 'schoolClass', 'subject', 'room'])
            ->where('schedule_id', $scheduleId)
            ->get();

        $teacherClashes = [];
        $classClashes = [];
        $roomClashes = [];
        $recommendations = [];

        // 1. Teacher double-booking check
        $byTeacherSlot = $items->groupBy(fn($i) => $i->teacher_id . '_' . $i->day . '_' . $i->period);
        foreach ($byTeacherSlot as $key => $group) {
            if ($group->count() > 1) {
                $teacher = $group->first()->teacher;
                $classes = $group->pluck('schoolClass.name')->join(' dan ');
                $day = $group->first()->day;
                $period = $group->first()->period;

                $conflictMsg = "Guru {$teacher->name} bentrok mengajar kelas {$classes} pada hari {$day} Jam ke-{$period}.";
                $teacherClashes[] = [
                    'teacher' => $teacher->name,
                    'day' => $day,
                    'period' => $period,
                    'classes' => $classes,
                    'message' => $conflictMsg,
                ];
                $recommendations[] = "Pindahkan salah satu jadwal {$classes} ke hari lain atau jam kosong guru {$teacher->name}.";
            }
        }

        // 2. Class double-booking check
        $byClassSlot = $items->groupBy(fn($i) => $i->class_id . '_' . $i->day . '_' . $i->period);
        foreach ($byClassSlot as $key => $group) {
            if ($group->count() > 1) {
                $class = $group->first()->schoolClass;
                $subjects = $group->pluck('subject.name')->join(' dan ');
                $day = $group->first()->day;
                $period = $group->first()->period;

                $classClashes[] = [
                    'class' => $class->name,
                    'day' => $day,
                    'period' => $period,
                    'subjects' => $subjects,
                    'message' => "Kelas {$class->name} memiliki 2 mapel bersamaan ({$subjects}) pada {$day} Jam ke-{$period}.",
                ];
                $recommendations[] = "Atur salah satu mata pelajaran ({$subjects}) ke slot kosong pada jadwal kelas {$class->name}.";
            }
        }

        // 3. Room double-booking check
        $byRoomSlot = $items->whereNotNull('room_id')->groupBy(fn($i) => $i->room_id . '_' . $i->day . '_' . $i->period);
        foreach ($byRoomSlot as $key => $group) {
            if ($group->count() > 1) {
                $room = $group->first()->room;
                $classes = $group->pluck('schoolClass.name')->join(' dan ');
                $day = $group->first()->day;
                $period = $group->first()->period;

                $roomClashes[] = [
                    'room' => $room->name,
                    'day' => $day,
                    'period' => $period,
                    'classes' => $classes,
                    'message' => "Ruangan {$room->name} digunakan secara bersamaan oleh kelas {$classes} pada {$day} Jam ke-{$period}.",
                ];
                $recommendations[] = "Gunakan laboratorium/ruangan alternatif yang masih kosong pada {$day} Jam ke-{$period}.";
            }
        }

        $totalConflicts = count($teacherClashes) + count($classClashes) + count($roomClashes);

        return [
            'has_conflicts' => $totalConflicts > 0,
            'total_conflicts' => $totalConflicts,
            'teacher_clashes' => $teacherClashes,
            'class_clashes' => $classClashes,
            'room_clashes' => $roomClashes,
            'recommendations' => array_unique($recommendations),
            'diagnostic_status' => $totalConflicts === 0 ? 'CONSTRAINTS_CLEAN' : 'CONFLICTS_DETECTED',
        ];
    }
}
