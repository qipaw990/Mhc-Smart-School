@extends('layouts.app')

@section('title', 'Log Notifikasi WhatsApp')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Rekap Presensi
        </a>
        <h4 class="fw-bold mb-1">
            <i class="fa-brands fa-whatsapp text-success me-2"></i>Log Notifikasi WhatsApp Terkirim
        </h4>
        <p class="text-muted mb-0 small">Riwayat otomatisasi pengiriman pesan WhatsApp presensi ke Orang Tua & Siswa.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.school.index') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-gear me-1"></i> Edit Template & Gateway WA
        </a>
        <a href="{{ route('attendance.qr') }}" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-qrcode me-1"></i> Mode QR Presensi
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card p-3 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label text-success fw-bold">Total Terkirim</div>
                    <div class="kpi-value text-dark">{{ number_format($totalSent) }}</div>
                    <div class="kpi-sub">Notifikasi Sukses</div>
                </div>
                <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                    <i class="fa-solid fa-circle-check fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card p-3 border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label text-danger fw-bold">Gagal / Dilewati</div>
                    <div class="kpi-value text-dark">{{ number_format($totalFailed) }}</div>
                    <div class="kpi-sub">Gagal Dikirim</div>
                </div>
                <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                    <i class="fa-solid fa-circle-xmark fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card p-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="kpi-label text-info fw-bold">Hari Ini</div>
                    <div class="kpi-value text-dark">{{ number_format($todayCount) }}</div>
                    <div class="kpi-sub">Pesan Hari Ini</div>
                </div>
                <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
                    <i class="fa-solid fa-paper-plane fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('attendance.wa-logs') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama penerima, nomor HP, atau isi pesan..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select bg-light">
                <option value="">-- Semua Status --</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>✅ Success (Terkirim)</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>❌ Failed (Gagal)</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control bg-light" value="{{ request('date') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>WAKTU & DATES</th>
                    <th>PENERIMA</th>
                    <th>NOMOR WA</th>
                    <th>TIPE</th>
                    <th>STATUS</th>
                    <th>ISI PESAN</th>
                    <th class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">{{ $log->recipient_name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="font-monospace text-dark fw-semibold">
                                <i class="fa-brands fa-whatsapp text-success me-1"></i>{{ $log->phone }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary text-uppercase">
                                {{ $log->type }}
                            </span>
                        </td>
                        <td>
                            @if($log->status == 'success')
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fa-solid fa-check-circle me-1"></i> Terkirim
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger" title="{{ $log->response_info }}">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Gagal
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-muted text-truncate" style="max-width: 260px;">
                                {{ Str::limit($log->message, 60) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewMessageModal{{ $log->id }}">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </button>
                        </td>
                    </tr>

                    <!-- View Message Modal -->
                    <div class="modal fade" id="viewMessageModal{{ $log->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">
                                        <i class="fa-brands fa-whatsapp text-success me-2"></i>Detail Pesan WhatsApp
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <div class="small text-secondary fw-semibold">Penerima & Nomor:</div>
                                        <div class="fw-bold text-dark">{{ $log->recipient_name ?? 'Penerima' }} ({{ $log->phone }})</div>
                                        <div class="small text-muted">Waktu: {{ $log->created_at->isoFormat('dddd, D MMMM Y - HH:mm:ss') }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Isi Pesan WhatsApp:</div>
                                        <div class="p-3 bg-light rounded border font-monospace small" style="white-space: pre-wrap; word-break: break-word;">{{ $log->message }}</div>
                                    </div>
                                    @if($log->response_info)
                                        <div>
                                            <div class="small text-secondary fw-semibold mb-1">Response Gateway:</div>
                                            <div class="p-2 bg-dark text-white rounded small font-monospace">{{ $log->response_info }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada log pesan WhatsApp yang dicatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
