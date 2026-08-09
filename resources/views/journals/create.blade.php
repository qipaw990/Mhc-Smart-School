@extends('layouts.app')

@section('title', 'Isi Jurnal Mengajar Digital')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('journals.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Jurnal
        </a>
        <h4 class="fw-bold mb-1">Catat Jurnal Mengajar Harian</h4>
        <p class="text-muted mb-0 small">Input terintegrasi: <strong>Jadwal Pelajaran &rarr; Rombel &rarr; TP Kurikulum Merdeka &rarr; Ringkasan KBM</strong>.</p>
    </div>
</div>

<div class="card card-custom p-4 p-md-5">
    <form action="{{ route('journals.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            {{-- 1. Guru Pengampu --}}
            @if($isGuru && $teacher)
                {{-- Teacher: auto-set, tampilkan sebagai text, bukan dropdown --}}
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Guru Pengampu</label>
                    <div class="form-control bg-light text-primary fw-semibold">
                        <i class="fa-solid fa-user-tie me-2"></i>{{ $teacher->full_name }}
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Guru Pengampu</label>
                    <select name="teacher_id" class="form-select" required>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- 2. Sesi Jadwal Terkait --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Sesi Jadwal Terkait (Opsional)</label>
                <select name="schedule_item_id" class="form-select">
                    <option value="">-- KBM Reguler Hari Ini --</option>
                    @foreach($scheduleItems as $si)
                        <option value="{{ $si->id }}" {{ (request('schedule_item_id') == $si->id) ? 'selected' : '' }}>
                            [{{ $si->day }} Jam {{ $si->period }}] {{ $si->schoolClass?->name }} - {{ $si->subject?->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Rombel Kelas --}}
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Rombel Kelas</label>
                @if($isGuru && $classes->count() === 1)
                    <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                    <div class="form-control bg-light fw-semibold">
                        <i class="fa-solid fa-users me-2 text-success"></i>{{ $classes->first()->name }}
                    </div>
                @else
                    <select name="class_id" class="form-select" required>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (request('class_id') == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->major?->code }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- 4. Mata Pelajaran --}}
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Mata Pelajaran</label>
                @if($isGuru && $subjects->count() === 1)
                    <input type="hidden" name="subject_id" value="{{ $subjects->first()->id }}">
                    <div class="form-control bg-light fw-semibold">
                        <i class="fa-solid fa-book me-2 text-primary"></i>{{ $subjects->first()->name }}
                    </div>
                @else
                    <select name="subject_id" class="form-select" required>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (request('subject_id') == $s->id) ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->code }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- 5. Tanggal --}}
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Pelaksanaan</label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jam Pelajaran Mulai</label>
                <input type="number" name="period_start" class="form-control" value="1" min="1" max="10" required>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Jam Pelajaran Selesai</label>
                <input type="number" name="period_end" class="form-control" value="3" min="1" max="10" required>
            </div>

            {{-- 6. Tujuan Pembelajaran --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Tujuan Pembelajaran (TP) yang Dicapai</label>
                <select name="learning_objective_id" class="form-select">
                    <option value="">-- Pilih TP Kurikulum Merdeka --</option>
                    @foreach($learningObjectives as $tp)
                        <option value="{{ $tp->id }}">{{ $tp->code }} - {{ Str::limit($tp->description, 60) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 7. Aktivitas KBM --}}
            <div class="col-md-12">
                <label class="form-label small fw-semibold">Ringkasan Aktivitas Pembelajaran (KBM)</label>
                <textarea name="topic_activity" class="form-control" rows="4" placeholder="Contoh: Menjelaskan sintaks perulangan For Loop, demonstrasi live coding di Lab Komputer, dan praktik penyelesaian kasus kasir minimarket berkelompok." required></textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-semibold">Catatan Hambatan / Kejadian Khusus di Kelas</label>
                <textarea name="notes_challenges" class="form-control" rows="3" placeholder="Contoh: 1 unit PC lab mengalami kendala jaringan LAN. Siswa bergabung dengan teman kelompok lainnya."></textarea>
            </div>

            {{-- 8. Rekap Kehadiran --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Jumlah Siswa Hadir</label>
                <input type="number" name="student_present_count" class="form-control" value="34" min="0" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Jumlah Siswa Tidak Hadir (S/I/A)</label>
                <input type="number" name="student_absent_count" class="form-control" value="2" min="0" required>
            </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-save me-2"></i> SIMPAN JURNAL MENGAJAR
            </button>
        </div>
    </form>
</div>
@endsection
