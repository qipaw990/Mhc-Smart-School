@extends('layouts.app')

@section('title', 'Detail Modul Ajar - ' . $module->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('curriculum.modules.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Modul
        </a>
        <h4 class="fw-bold mb-1">{{ $module->title }}</h4>
        <p class="text-muted mb-0 small">{{ $module->subject?->name }} | Fase {{ $module->phase }} (Kelas {{ $module->grade_level }}) | Penyusun: <strong>{{ $module->teacher?->full_name }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('curriculum.modules.edit', $module->id) }}" class="btn btn-outline-warning btn-sm text-dark fw-bold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Modul Ajar
        </a>
        <a href="{{ route('curriculum.modules.print', $module->id) }}" target="_blank" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-print me-1"></i> Cetak / Export PDF Resmi
        </a>
    </div>
</div>

<div class="card card-custom p-4 p-md-5 mb-4">
    <!-- Header Modul -->
    <div class="border-bottom pb-4 mb-4 text-center">
        <h4 class="fw-bold text-dark mb-1">MODUL AJAR KURIKULUM MERDEKA</h4>
        <h5 class="text-primary fw-bold mb-2">{{ $module->subject?->name }}</h5>
        <div class="text-muted small">{{ $school->name }} | Tahun Ajaran 2026/2027</div>
    </div>

    <!-- I. Informasi Umum -->
    <div class="mb-4">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">I. INFORMASI UMUM</h5>
        <div class="row g-3 small">
            <div class="col-md-6"><strong>Nama Penyusun:</strong> {{ $module->teacher?->full_name }}</div>
            <div class="col-md-6"><strong>Satuan Pendidikan:</strong> {{ $school->name }}</div>
            <div class="col-md-6"><strong>Mata Pelajaran:</strong> {{ $module->subject?->name }}</div>
            <div class="col-md-6"><strong>Fase / Kelas:</strong> Fase {{ $module->phase }} / Kelas {{ $module->grade_level }}</div>
            <div class="col-md-6"><strong>Alokasi Waktu:</strong> {{ $module->allocated_hours }} Jam Pelajaran (JP)</div>
            <div class="col-md-6"><strong>Model Pembelajaran:</strong> <span class="badge bg-primary bg-opacity-10 text-primary">{{ $module->learning_model }}</span></div>
            <div class="col-md-6"><strong>Metode:</strong> {{ $module->methods }}</div>
            <div class="col-md-6"><strong>Target Peserta Didik:</strong> {{ $module->target_students }}</div>
        </div>
    </div>

    <!-- II. Komponen Inti -->
    <div class="mb-4">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">II. KOMPONEN INTI</h5>
        <div class="mb-3">
            <div class="fw-bold text-secondary small">A. Elemen Capaian Pembelajaran (CP):</div>
            <div class="p-3 bg-light rounded border small mt-1">
                <strong>{{ $module->learningOutcome?->code }} - {{ $module->learningOutcome?->element }}:</strong><br>
                {{ $module->learningOutcome?->description }}
            </div>
        </div>

        <div class="mb-3">
            <div class="fw-bold text-secondary small">B. Tujuan Pembelajaran (TP):</div>
            <div class="p-3 bg-light rounded border small mt-1">
                <strong>{{ $module->learningObjective?->code }}:</strong> {{ $module->learningObjective?->description }}
            </div>
        </div>

        <div class="mb-4">
            <div class="fw-bold text-secondary small mb-2">C. Skenario Kegiatan Pembelajaran:</div>
            
            <div class="card p-3 mb-2 border-start border-primary border-3">
                <div class="fw-bold text-primary small mb-1">1. Kegiatan Pendahuluan (15 Menit)</div>
                <div class="small whitespace-pre-line">{{ $module->preliminary_activities }}</div>
            </div>

            <div class="card p-3 mb-2 border-start border-info border-3">
                <div class="fw-bold text-info small mb-1">2. Kegiatan Inti (150 Menit)</div>
                <div class="small whitespace-pre-line">{{ $module->core_activities }}</div>
            </div>

            <div class="card p-3 border-start border-success border-3">
                <div class="fw-bold text-success small mb-1">3. Kegiatan Penutup (15 Menit)</div>
                <div class="small whitespace-pre-line">{{ $module->closing_activities }}</div>
            </div>
        </div>
    </div>

    <!-- III. Asesmen -->
    <div class="mb-4">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">III. ASESMEN & EVALUASI</h5>
        <div class="row g-3 small">
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-light">
                    <div class="fw-bold text-dark mb-1">Asesmen Awal (Diagnostik):</div>
                    <div>{{ $module->diagnostic_assessment ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-light">
                    <div class="fw-bold text-dark mb-1">Asesmen Proses (Formatif):</div>
                    <div>{{ $module->formative_assessment ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 h-100 bg-light">
                    <div class="fw-bold text-dark mb-1">Asesmen Akhir (Sumatif):</div>
                    <div>{{ $module->summative_assessment ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3 small mt-2">
            <div class="col-md-6">
                <div class="p-3 border rounded">
                    <strong>Program Remedial:</strong> {{ $module->remedial_plan ?? '-' }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded">
                    <strong>Program Pengayaan:</strong> {{ $module->enrichment_plan ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- IV. Lampiran -->
    <div>
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">IV. LAMPIRAN</h5>
        @if($module->student_worksheet)
            <div class="mb-3">
                <div class="fw-bold text-secondary small mb-1">A. Lembar Kerja Peserta Didik (LKPD):</div>
                <div class="p-3 bg-light rounded border small font-monospace whitespace-pre-line">{{ $module->student_worksheet }}</div>
            </div>
        @endif

        @if($module->assessment_rubric)
            <div class="mb-3">
                <div class="fw-bold text-secondary small mb-1">B. Rubrik Penilaian:</div>
                <div class="p-3 bg-light rounded border small font-monospace whitespace-pre-line">{{ $module->assessment_rubric }}</div>
            </div>
        @endif
    </div>

    <!-- Tanda Tangan Pengesahan -->
    <div class="row mt-5 pt-4 text-center small">
        <div class="col-6">
            <div>Mengetahui,</div>
            <div>Kepala {{ $school->name }}</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-decoration-underline">{{ $school->principal_name }}</div>
            <div>NIP. 197503122000031001</div>
        </div>
        <div class="col-6">
            <div>Bogor, {{ date('d F Y') }}</div>
            <div>Guru Mata Pelajaran</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-decoration-underline">{{ $module->teacher?->full_name }}</div>
            <div>NIP. {{ $module->teacher?->nip ?? '-' }}</div>
        </div>
    </div>
</div>

<style>
    .whitespace-pre-line {
        white-space: pre-line;
    }
</style>
@endsection
