@extends('layouts.app')

@section('title', 'Detail Jurnal Mengajar')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('journals.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Jurnal
        </a>
        <h4 class="fw-bold mb-1">Detail Jurnal Mengajar Digital</h4>
        <p class="text-muted mb-0 small">{{ $journal->schoolClass?->name }} | {{ $journal->subject?->name }} | {{ $journal->date->format('d F Y') }}</p>
    </div>
</div>

<div class="card card-custom p-4 p-md-5">
    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <span class="badge bg-primary fs-6 px-3 py-1.5 fw-bold mb-2">{{ $journal->schoolClass?->name }}</span>
            <h4 class="fw-bold text-dark mb-1">{{ $journal->subject?->name }}</h4>
            <div class="text-muted small">Guru Pengampu: <strong>{{ $journal->teacher?->full_name }}</strong></div>
        </div>
        <div class="text-end">
            <div class="badge bg-success fs-6"><i class="fa-solid fa-circle-check me-1"></i> Terverifikasi Sistem</div>
            <div class="text-muted small mt-1">Tanggal: {{ $journal->date->format('d F Y') }} (Jam Ke-{{ $journal->period_start }} s/d {{ $journal->period_end }})</div>
        </div>
    </div>

    <!-- Informasi Detail KBM -->
    <div class="row g-4 mb-4">
        @if($journal->learningObjective)
            <div class="col-12">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold text-primary small mb-1">
                        <i class="fa-solid fa-bullseye me-1"></i> Tujuan Pembelajaran (TP Kurikulum Merdeka):
                    </div>
                    <div class="small text-dark">
                        <strong>{{ $journal->learningObjective->code }}:</strong> {{ $journal->learningObjective->description }}
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-list-check text-primary me-2"></i>Aktivitas Pembelajaran (KBM):</h6>
            <div class="p-4 bg-light rounded-3 border small whitespace-pre-line text-dark">
                {{ $journal->topic_activity }}
            </div>
        </div>

        @if($journal->notes_challenges)
            <div class="col-12">
                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-note-sticky text-warning me-2"></i>Catatan Kejadian Khusus / Kendala:</h6>
                <div class="p-3 bg-light rounded-3 border-start border-warning border-4 small text-dark">
                    {{ $journal->notes_challenges }}
                </div>
            </div>
        @endif
    </div>

    <!-- Rekap Kehadiran Siswa -->
    <div class="border rounded-3 p-3 bg-light d-flex justify-content-around text-center mb-4">
        <div>
            <div class="text-muted small fw-semibold">SISWA HADIR</div>
            <h4 class="fw-bold text-success mb-0">{{ $journal->student_present_count }}</h4>
        </div>
        <div class="border-end"></div>
        <div>
            <div class="text-muted small fw-semibold">SISWA TIDAK HADIR</div>
            <h4 class="fw-bold text-danger mb-0">{{ $journal->student_absent_count }}</h4>
        </div>
        <div class="border-end"></div>
        <div>
            <div class="text-muted small fw-semibold">TOTAL SISWA KELAS</div>
            <h4 class="fw-bold text-primary mb-0">{{ $journal->student_present_count + $journal->student_absent_count }}</h4>
        </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="row mt-5 pt-3 text-center small">
        <div class="col-6">
            <div>Mengetahui,</div>
            <div>Kepala {{ $school->name }}</div>
            <div style="height: 60px;"></div>
            <div class="fw-bold text-decoration-underline">{{ $school->principal_name }}</div>
        </div>
        <div class="col-6">
            <div>Guru Pengampu Mata Pelajaran</div>
            <div>{{ $school->name }}</div>
            <div style="height: 60px;"></div>
            <div class="fw-bold text-decoration-underline">{{ $journal->teacher?->full_name }}</div>
        </div>
    </div>
</div>

<style>
    .whitespace-pre-line {
        white-space: pre-line;
    }
</style>
@endsection
