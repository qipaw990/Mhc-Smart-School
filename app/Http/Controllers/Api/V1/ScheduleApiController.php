<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ScheduleItem;
use Illuminate\Http\Request;

class ScheduleApiController extends Controller
{
    /**
     * Get Personal Weekly Schedule (Siswa / Guru)
     */
    public function mySchedule(Request $request)
    {
        $user = $request->user();

        $query = ScheduleItem::with(['subject', 'teacher', 'schoolClass', 'room']);

        if ($user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        } elseif ($user->student && $user->student->current_class_id) {
            $query->where('class_id', $user->student->current_class_id);
        }

        $items = $query->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('period')
            ->get();

        // Group by day for clean Android UI rendering
        $scheduleByDay = $items->groupBy('day');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user_name'  => $user->name,
                'role'       => $user->roles->first()?->name,
                'schedule'   => $scheduleByDay,
            ],
        ]);
    }

    /**
     * Get Full Schedule Matrix (Filtered by class_id or teacher_id)
     */
    public function matrix(Request $request)
    {
        $query = ScheduleItem::with(['subject', 'teacher', 'schoolClass', 'room', 'timeSlot']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $matrix = $query->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('period')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $matrix,
        ]);
    }
}
