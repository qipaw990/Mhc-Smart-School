@extends('layouts.app')

@section('title', 'Kehadiran Saya - ' . ($student->name ?? 'Siswa'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-calendar-check me-2 text-success"></i>Rekap Kehadiran Saya</h4>
        <p class="text-muted mb-0 small">
            Tahun Ajaran: <strong>{{ $ay->name ?? '-' }}</strong> &nbsp;|&nbsp;
            Kelas: <strong>{{ $student->currentClass->name ?? '-' }}</strong> &nbsp;|&nbsp;
            Nama: <strong>{{ $student->name }}</strong>
        </p>
    </div>
    <a href="{{ route('student.nilai') }}" class="btn btn-outline-primary btn-sm">
        <i class="fa-solid fa-chart-bar me-1"></i> Lihat Nilai
    </a>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-dark">{{ $summary['total'] }}</div>
            <div class="small text-muted">Total Pertemuan</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-success">{{ $summary['hadir'] }}</div>
            <div class="small text-muted">Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-info">{{ $summary['sakit'] }}</div>
            <div class="small text-muted">Sakit</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-warning">{{ $summary['izin'] }}</div>
            <div class="small text-muted">Izin</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm">
            <div class="fs-2 fw-bold text-danger">{{ $summary['alpha'] }}</div>
            <div class="small text-muted">Alpha</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center border-0 shadow-sm"
             style="background: {{ $summary['persen_hadir'] >= 75 ? '#f0fdf4' : '#fff7ed' }};">
            <div class="fs-2 fw-bold text-{{ $summary['persen_hadir'] >= 75 ? 'success' : 'danger' }}">
                {{ $summary['persen_hadir'] }}%
            </div>
            <div class="small text-muted">% Kehadiran</div>
        </div>
    </div>
</div>

{{-- Progress Bar --}}
@if($summary['total'] > 0)
<div class="card card-custom p-3 mb-4">
    <div class="d-flex justify-content-between small fw-semibold mb-1">
        <span>Tingkat Kehadiran</span>
        <span class="text-{{ $summary['persen_hadir'] >= 75 ? 'success' : 'danger' }}">
            {{ $summary['persen_hadir'] }}%
            @if($summary['persen_hadir'] >= 75)
                <i class="fa-solid fa-circle-check ms-1"></i>
            @else
                <i class="fa-solid fa-triangle-exclamation ms-1"></i> Perlu Perhatian!
            @endif
        </span>
    </div>
    <div class="progress" style="height: 14px; border-radius: 8px;">
        <div class="progress-bar bg-{{ $summary['persen_hadir'] >= 75 ? 'success' : 'danger' }}"
             role="progressbar" style="width: {{ $summary['persen_hadir'] }}%;"
             aria-valuenow="{{ $summary['persen_hadir'] }}" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
    <div class="small text-muted mt-1">Minimal kehadiran 75% untuk memenuhi syarat ujian.</div>
</div>
@endif

{{-- Detail per Bulan --}}
@if($records->isEmpty())
    <div class="card card-custom p-5 text-center">
        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">Belum ada data kehadiran untuk tahun ajaran ini.</h6>
    </div>
@else
    @foreach($byMonth->sortKeysDesc() as $monthKey => $monthRecords)
    @php
        [$year, $month] = explode('-', $monthKey);
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        $hadirCount = $monthRecords->where('status', 'hadir')->count();
        $totalCount = $monthRecords->count();
    @endphp
    <div class="card card-custom mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2 px-3"
             style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div class="fw-bold small d-flex align-items-center gap-2">
                <i class="fa-regular fa-calendar text-primary"></i>
                {{ $monthName }}
            </div>
            <div class="small text-muted">
                <span class="badge bg-success me-1">{{ $hadirCount }} Hadir</span>
                <span class="badge bg-secondary">{{ $totalCount - $hadirCount }} Tidak Hadir</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Tanggal</th>
                            <th>Mata Pelajaran</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th class="text-center">Waktu</th>
                            <th class="text-center">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthRecords->sortByDesc('date') as $rec)
                        @php
                            $statusColor = match($rec->status) {
                                'hadir'  => 'success',
                                'sakit'  => 'info',
                                'izin'   => 'warning',
                                'alpha'  => 'danger',
                                default  => 'secondary',
                            };
                            $statusLabel = match($rec->status) {
                                'hadir'  => '✓ Hadir',
                                'sakit'  => '🤒 Sakit',
                                'izin'   => '📋 Izin',
                                'alpha'  => '✗ Alpha',
                                default  => ucfirst($rec->status),
                            };
                        @endphp
                        <tr>
                            <td class="px-3 fw-semibold">{{ $rec->date->format('d M Y') }}</td>
                            <td>{{ $rec->scheduleItem?->subject?->name ?? 'Umum' }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ strtoupper($rec->type ?? '-') }}
                                </span>
                            </td>
                            <td class="text-capitalize text-muted">{{ $rec->method ?? '-' }}</td>
                            <td class="text-center text-muted">{{ $rec->time ? \Carbon\Carbon::parse($rec->time)->format('H:i') : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-muted" style="max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $rec->notes ?? '-' }}
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
