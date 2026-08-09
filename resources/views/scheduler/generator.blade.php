@extends('layouts.app')

@section('title', 'Auto Scheduler (CSP Optimization Engine)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Automatic School Scheduler Engine</h4>
        <p class="text-muted mb-0 small">Mesin penjadwalan otomatis berbasis <strong>Constraint Satisfaction & Penalty Optimization</strong>.</p>
    </div>
    <a href="{{ route('scheduler.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks Jadwal
    </a>
</div>

<div class="row g-4">
    <!-- Generator Launcher Card -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Generate / Regenerate Jadwal</h5>
            <p class="small text-muted mb-4">
                Sistem akan menyusun jadwal otomatis dengan meminimalkan bentrok (Hard Constraints) dan mengoptimalkan pembelajaran blok praktik (Soft Constraints).
            </p>

            <div class="border rounded-3 p-3 bg-light mb-4 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Beban Mengajar Terpetakan:</span>
                    <strong>{{ $totalLoads }} JP / minggu</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tahun Ajaran Aktif:</span>
                    <strong>{{ $ay?->name }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Constraint Satisfaction:</span>
                    <span class="badge bg-success">Hard + Soft Optimizer</span>
                </div>
            </div>

            <form action="{{ route('scheduler.generate') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama / Versi Jadwal</label>
                    <input type="text" name="schedule_name" class="form-control" value="Jadwal Reguler Kurikulum Merdeka" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow" style="background-color: var(--mhc-primary); border: none;">
                    <i class="fa-solid fa-cogs me-1"></i> JALANKAN AUTO SCHEDULER ENGINE
                </button>
            </form>
        </div>
    </div>

    <!-- Generated Schedules History -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left text-info me-2"></i>Histori Versi Jadwal Sekolah</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>VERSI JADWAL</th>
                            <th>TOTAL SESI</th>
                            <th>SCORE</th>
                            <th>STATUS</th>
                            <th>TANGGAL GENERATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $s)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $s->name }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Versi: {{ $s->version }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $s->items_count }} Sesi Mapel</span></td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold fs-6">
                                        {{ $s->optimization_score }}%
                                    </span>
                                </td>
                                <td>
                                    @if($s->status === 'active')
                                        <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Aktif Digunakan</span>
                                    @else
                                        <span class="badge bg-secondary">Arsip</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $s->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada histori jadwal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
