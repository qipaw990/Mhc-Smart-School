@extends('layouts.app')

@section('title', isset($isGuru) && $isGuru ? 'Jadwal Mengajar Saya' : 'Jadwal Pelajaran Sekolah')

@section('content')
@php $isGuruView = isset($isGuru) && $isGuru && isset($teacher); @endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        @if($isGuruView)
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-calendar-week text-primary me-2"></i>Jadwal Mengajar Saya</h4>
            <p class="text-muted mb-0 small">
                <i class="fa-solid fa-user-tie me-1"></i> {{ $teacher->full_name }}
                &nbsp;&bull;&nbsp; Jadwal Aktif: <strong>{{ $activeSchedule?->name ?? '-' }}</strong>
            </p>
        @else
            <h4 class="fw-bold mb-1">Matriks Jadwal Pelajaran Sekolah</h4>
            <p class="text-muted mb-0 small">
                Versi Aktif: <strong>{{ $activeSchedule?->name ?? 'Belum ada jadwal aktif' }}</strong> |
                Optimization Score: <span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ $activeSchedule?->optimization_score ?? 100 }}%</span>
            </p>
        @endif
    </div>
    @if(!$isGuruView)
    <div class="d-flex gap-2">
        <a href="{{ route('scheduler.generator') }}" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto Scheduler
        </a>
        <a href="{{ route('scheduler.conflicts') }}" class="btn btn-outline-danger btn-sm fw-bold">
            <i class="fa-solid fa-shield-halved me-1"></i> Cek Bentrok
        </a>
    </div>
    @endif
</div>

@if($isGuruView)
    {{-- Teacher: show subject/class summary instead of filter bar --}}
    @php
        $mySubjects = $items->flatten()->groupBy(fn($i) => $i->subject_id)->map(fn($g) => $g->first());
        $myClasses  = $items->flatten()->groupBy(fn($i) => $i->class_id)->map(fn($g) => $g->first());
        $totalJp    = $items->flatten()->count();
    @endphp
    <div class="card card-custom mb-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%); border-left: 3px solid var(--kem-primary) !important;">
        <div style="padding: 0.7rem 1rem;">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary" style="font-size: 0.72rem;"><i class="fa-solid fa-book-open me-1"></i>{{ $mySubjects->count() }} Mata Pelajaran</span>
                    <span class="badge bg-success" style="font-size: 0.72rem;"><i class="fa-solid fa-users me-1"></i>{{ $myClasses->count() }} Rombel Kelas</span>
                    <span class="badge bg-warning text-dark" style="font-size: 0.72rem;"><i class="fa-solid fa-clock me-1"></i>{{ $totalJp }} JP/Minggu</span>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <a href="{{ route('journals.create') }}" class="btn btn-xs btn-outline-primary">
                        <i class="fa-solid fa-pen-nib me-1"></i>Isi Jurnal
                    </a>
                    <a href="{{ route('gradebook.create') }}" class="btn btn-xs btn-outline-success">
                        <i class="fa-solid fa-table-cells me-1"></i>Input Nilai
                    </a>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Admin: full filter bar --}}
    <div class="card card-custom p-3 mb-3">
        <form action="{{ route('scheduler.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1"><i class="fa-solid fa-chalkboard-user me-1"></i> Rombel Kelas:</label>
                <select name="class_id" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->major?->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1"><i class="fa-solid fa-user-tie me-1"></i> Guru Pengampu:</label>
                <select name="teacher_id" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Pilih Guru --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $selectedTeacherId == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary mb-1"><i class="fa-solid fa-door-open me-1"></i> Ruangan / Lab:</label>
                <select name="room_id" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Pilih Ruangan --</option>
                    @foreach($rooms as $r)
                        <option value="{{ $r->id }}" {{ $selectedRoomId == $r->id ? 'selected' : '' }}>{{ $r->code }} ({{ $r->name }})</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
@endif

<!-- Timetable Matrix Grid -->
<div class="card card-custom p-4">
    @php
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $periodDefinitions = [
            1 => ['07:00 - 07:45', false, 'Jam Ke-1'],
            2 => ['07:45 - 08:30', false, 'Jam Ke-2'],
            3 => ['08:30 - 09:15', false, 'Jam Ke-3'],
            4 => ['09:15 - 09:45', true,  'Istirahat Pagi'],
            5 => ['09:45 - 10:30', false, 'Jam Ke-4'],
            6 => ['10:30 - 11:15', false, 'Jam Ke-5'],
            7 => ['11:15 - 12:00', false, 'Jam Ke-6'],
            8 => ['12:00 - 12:45', true,  'Istirahat & Sholat'],
            9 => ['12:45 - 13:30', false, 'Jam Ke-7'],
            10 => ['13:30 - 14:15', false, 'Jam Ke-8'],
        ];
    @endphp

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 140px;">WAKTU / JAM</th>
                    @foreach($days as $day)
                        <th style="width: 17%;">{{ strtoupper($day) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periodDefinitions as $p => $info)
                    @if($info[1])
                        <!-- Break Time Row -->
                        <tr class="table-secondary text-muted fw-bold">
                            <td class="small">{{ $info[0] }}</td>
                            <td colspan="5" class="py-2 small">
                                <i class="fa-solid fa-mug-hot me-1 text-warning"></i> {{ $info[2] }} ({{ $info[0] }})
                            </td>
                        </tr>
                    @else
                        <!-- Lesson Period Row -->
                        <tr>
                            <td class="bg-light text-start small">
                                <div class="fw-bold text-dark">{{ $info[2] }}</div>
                                <div class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $info[0] }}</div>
                            </td>

                            @foreach($days as $day)
                                @php
                                    $sessionItems = $items->get($day . '_' . $p, collect());
                                @endphp
                                <td class="p-2 align-middle">
                                    @forelse($sessionItems as $item)
                                        <div class="p-2 rounded-3 text-start shadow-sm border {{ in_array($item->subject->type, ['practice', 'theory_practice']) ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light border-secondary' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge bg-primary text-white font-monospace" style="font-size: 0.7rem;">{{ $item->subject->code }}</span>
                                                <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ $item->schoolClass->name }}</span>
                                            </div>
                                            <div class="fw-bold text-dark small" style="font-size: 0.8rem; line-height: 1.2;">{{ $item->subject->name }}</div>
                                            <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-user-tie me-1"></i> {{ $item->teacher->name }}
                                            </div>
                                            @if($item->room)
                                                <div class="text-info small fw-semibold mt-0.5" style="font-size: 0.7rem;">
                                                    <i class="fa-solid fa-location-dot me-1"></i> {{ $item->room->code }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted opacity-25 small">-</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
