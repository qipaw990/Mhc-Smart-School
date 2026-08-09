@extends('layouts.app')

@section('title', 'Analisis Hasil & Butir Soal - ' . $exam->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('cbt.exams.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Jadwal Ujian
        </a>
        <h4 class="fw-bold mb-1">Hasil & Analisis Butir Soal: {{ $exam->title }}</h4>
        <p class="text-muted mb-0 small">{{ $exam->subject?->name }} | Guru: <strong>{{ $exam->teacher?->full_name }}</strong> | KKTP Minimal: <strong>{{ $exam->kktp_score }}</strong></p>
    </div>
</div>

<!-- Exam Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-primary border-4 text-center">
            <div class="text-muted small fw-semibold">RATA-RATA SKOR</div>
            <h3 class="fw-bold text-primary mb-0">{{ number_format($avgScore, 1) }}</h3>
            <div class="text-muted small">Tertinggi: {{ $maxScore }} | Terendah: {{ $minScore }}</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-success border-4 text-center">
            <div class="text-muted small fw-semibold">LULUS KKTP</div>
            <h3 class="fw-bold text-success mb-0">{{ $passedCount }}</h3>
            <div class="text-muted small">{{ $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100) : 0 }}% Tingkat Kelulusan</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-danger border-4 text-center">
            <div class="text-muted small fw-semibold">BELUM LULUS (REMEDIAL)</div>
            <h3 class="fw-bold text-danger mb-0">{{ $failedCount }}</h3>
            <div class="text-muted small">Peserta Ujian</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-info border-4 text-center">
            <div class="text-muted small fw-semibold">TOTAL PESERTA</div>
            <h3 class="fw-bold text-info mb-0">{{ $totalStudents }}</h3>
            <div class="text-muted small">Lembar Jawaban Masuk</div>
        </div>
    </div>
</div>

<!-- 1. Rekap Nilai Peserta Ujian -->
<div class="card card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-users text-primary me-2"></i>Rekapitulasi Nilai Siswa:</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>NO</th>
                    <th>NISN</th>
                    <th>NAMA SISWA</th>
                    <th>ROMBEL</th>
                    <th>DURASI PENGERJAAN</th>
                    <th>STATUS KKTP</th>
                    <th>SKOR TOTAL</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentExams as $idx => $se)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="font-monospace text-muted">{{ $se->student?->nisn }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $se->student?->name }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $se->student?->currentClass?->name }}</span></td>
                        <td>{{ round($se->duration_used_seconds / 60) }} Menit</td>
                        <td>
                            @if($se->is_passed)
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Tuntas KKTP</span>
                            @else
                                <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Belum Tuntas</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold fs-6 {{ $se->is_passed ? 'text-success' : 'text-danger' }}">
                                {{ $se->total_score }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('cbt.analytics.student-detail', $se->id) }}" class="btn btn-xs btn-outline-primary fw-bold">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Koreksi / Lembar Jawaban
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data nilai ujian peserta.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Analisis Butir Soal (Item Analysis) -->
<div class="card card-custom p-4">
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-line text-success me-2"></i>Analisis Kualitas & Tingkat Kesukaran Butir Soal:</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th>PERTANYAAN SOAL</th>
                    <th style="width: 140px;">TIPE & KOGNITIF</th>
                    <th style="width: 100px;" class="text-center">JAWABAN BENAR</th>
                    <th style="width: 120px;" class="text-center">INDEKS KESUKARAN (P)</th>
                    <th style="width: 120px;" class="text-center">KATEGORI SOAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemAnalytics as $ia)
                    <tr>
                        <td class="text-center fw-bold">{{ $ia['question']->order_number }}</td>
                        <td>
                            <div class="text-dark">{{ Str::limit($ia['question']->question_text, 100) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $ia['question']->type_label }}</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning text-uppercase">{{ $ia['question']->cognitive_level }}</span>
                        </td>
                        <td class="text-center">
                            <strong>{{ $ia['correct_count'] }}</strong> / {{ $ia['total_answered'] }} Siswa
                        </td>
                        <td class="text-center font-monospace fw-bold">
                            {{ $ia['difficulty_index'] }}
                        </td>
                        <td class="text-center">
                            @if($ia['difficulty_category'] === 'Mudah')
                                <span class="badge bg-success">Mudah</span>
                            @elseif($ia['difficulty_category'] === 'Sedang')
                                <span class="badge bg-primary">Sedang (Ideal)</span>
                            @else
                                <span class="badge bg-danger">Sukar</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
