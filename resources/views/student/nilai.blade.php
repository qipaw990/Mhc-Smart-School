@extends('layouts.app')

@section('title', 'Nilai Saya - ' . ($student->name ?? 'Siswa'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Rekap Nilai Saya</h4>
        <p class="text-muted mb-0 small">
            Tahun Ajaran: <strong>{{ $ay->name ?? '-' }}</strong> &nbsp;|&nbsp;
            Kelas: <strong>{{ $student->currentClass->name ?? '-' }}</strong> &nbsp;|&nbsp;
            Nama: <strong>{{ $student->name }}</strong>
        </p>
    </div>
    <a href="{{ route('student.kehadiran') }}" class="btn btn-outline-primary btn-sm">
        <i class="fa-solid fa-calendar-check me-1"></i> Lihat Kehadiran
    </a>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @php $totalSubjects = count($bySubject); $passCount = collect($subjectAverages)->filter(fn($v) => $v !== null && $v >= 70)->count(); @endphp
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-primary">{{ $totalSubjects }}</div>
            <div class="small text-muted">Total Mata Pelajaran</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-success">{{ $passCount }}</div>
            <div class="small text-muted">Mata Pelajaran Tuntas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-warning">{{ $totalSubjects - $passCount }}</div>
            <div class="small text-muted">Perlu Perbaikan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            @php
                $validAvg = collect($subjectAverages)->filter(fn($v) => $v !== null);
                $overallAvg = $validAvg->isNotEmpty() ? round($validAvg->average(), 1) : '-';
            @endphp
            <div class="fs-2 fw-bold text-info">{{ $overallAvg }}</div>
            <div class="small text-muted">Rata-Rata Semua Mapel</div>
        </div>
    </div>
</div>

@if($bySubject->isEmpty())
    <div class="card card-custom p-5 text-center">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">Belum ada data nilai untuk tahun ajaran ini.</h6>
        <p class="small text-muted">Data nilai akan muncul setelah guru menginput asesmen.</p>
    </div>
@else
    @foreach($bySubject as $subjectName => $assessments)
    @php
        $avg = $subjectAverages[$subjectName] ?? null;
        $isTuntas = $avg !== null && $avg >= 70;
    @endphp
    <div class="card card-custom mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2 px-3"
             style="background: {{ $isTuntas ? '#f0fdf4' : '#fff7ed' }}; border-bottom: 1px solid {{ $isTuntas ? '#bbf7d0' : '#fed7aa' }};">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-book text-{{ $isTuntas ? 'success' : 'warning' }}"></i>
                <span class="fw-bold small">{{ $subjectName }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($avg !== null)
                    <span class="badge bg-{{ $isTuntas ? 'success' : 'warning' }} text-{{ $isTuntas ? 'white' : 'dark' }}">
                        Rata-rata: {{ $avg }}
                    </span>
                    <span class="badge bg-{{ $isTuntas ? 'success' : 'warning' }}-subtle border border-{{ $isTuntas ? 'success' : 'warning' }} text-{{ $isTuntas ? 'success' : 'warning' }}">
                        {{ $isTuntas ? '✓ Tuntas' : '⚠ Belum Tuntas' }}
                    </span>
                @else
                    <span class="badge bg-secondary">Belum dinilai</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Judul Asesmen</th>
                            <th>Tipe</th>
                            <th>Tanggal</th>
                            <th class="text-center">KKTP</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $a)
                        @php
                            $score = $a->scores->first();
                            $finalScore = $score?->final_score;
                            $kktp = $a->kktp_score ?? 70;
                            $isTuntasItem = $finalScore !== null && $finalScore >= $kktp;
                        @endphp
                        <tr>
                            <td class="px-3 fw-semibold">{{ $a->title }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ strtoupper($a->type ?? '-') }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $a->date?->format('d M Y') ?? '-' }}</td>
                            <td class="text-center text-muted">{{ $kktp }}</td>
                            <td class="text-center">
                                @if($score)
                                    {{ number_format($score->score, 0) }}
                                    @if($score->is_remedial)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Remedial</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold {{ $finalScore !== null ? ($isTuntasItem ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                {{ $finalScore !== null ? number_format($finalScore, 0) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($finalScore !== null)
                                    <span class="badge bg-{{ $isTuntasItem ? 'success' : 'danger' }}">
                                        {{ $isTuntasItem ? 'Tuntas' : 'Belum' }}
                                    </span>
                                @else
                                    <span class="text-muted small">Menunggu</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection
