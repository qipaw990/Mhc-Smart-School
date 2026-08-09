@extends('layouts.app')

@section('title', 'Live Monitor Proktor CBT - ' . $exam->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('cbt.exams.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Jadwal Ujian
        </a>
        <h4 class="fw-bold mb-1">Live Monitoring Proktor CBT: {{ $exam->title }}</h4>
        <p class="text-muted mb-0 small">Pemantauan status ujian peserta, durasi pengerjaan, dan <strong>deteksi pelanggaran tab browser secara realtime</strong>.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('cbt.exams.refresh-token', $exam->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                <i class="fa-solid fa-rotate me-1"></i> Ganti / Refresh Token
            </button>
        </form>
        <button onclick="location.reload()" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh Data
        </button>
    </div>
</div>

<!-- Proctor Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-danger border-4 text-center">
            <div class="text-muted small fw-semibold">TOKEN AKTIF UJIAN</div>
            <h2 class="fw-bold text-danger font-monospace mb-0">{{ $exam->token }}</h2>
            <div class="text-muted small">Berikan kepada siswa di ruangan</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-primary border-4 text-center">
            <div class="text-muted small fw-semibold">TOTAL PESERTA HADIR</div>
            <h3 class="fw-bold text-primary mb-0">{{ $totalParticipants }}</h3>
            <div class="text-muted small">Siswa Mengikuti Sesi</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-warning border-4 text-center">
            <div class="text-muted small fw-semibold">SEDANG MENGERJAKAN</div>
            <h3 class="fw-bold text-warning mb-0">{{ $inProgressCount }}</h3>
            <div class="text-muted small">Siswa Aktif di Workspace</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-success border-4 text-center">
            <div class="text-muted small fw-semibold">SELESAI / SUBMITTED</div>
            <h3 class="fw-bold text-success mb-0">{{ $submittedCount }}</h3>
            <div class="text-muted small">Lembar Jawaban Masuk</div>
        </div>
    </div>
</div>

<!-- Live Student Progress Table -->
<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Daftar Aktivitas Peserta Ujian:</h6>
        <span class="badge bg-success"><i class="fa-solid fa-circle-dot me-1"></i> Live Stream Online</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>NISN</th>
                    <th>NAMA SISWA</th>
                    <th>ROMBEL KELAS</th>
                    <th>WAKTU MULAI</th>
                    <th>STATUS UJIAN</th>
                    <th>PELANGGARAN TAB</th>
                    <th>SKOR REALTIME</th>
                    <th>IP ADDRESS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentExams as $se)
                    <tr>
                        <td class="font-monospace text-muted">{{ $se->student?->nisn }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $se->student?->name }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $se->student?->currentClass?->name }}</span></td>
                        <td>{{ $se->start_time->format('H:i:s') }}</td>
                        <td>
                            @if($se->status === 'in_progress')
                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner fa-spin me-1"></i> Mengerjakan</span>
                            @elseif($se->status === 'submitted')
                                <span class="badge bg-success"><i class="fa-solid fa-check-double me-1"></i> Selesai (Submitted)</span>
                            @elseif($se->status === 'blocked')
                                <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Terblokir (Pelanggaran)</span>
                            @endif
                        </td>
                        <td>
                            @if($se->tab_switch_count > 0)
                                <span class="badge bg-danger fs-6 px-2.5 py-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $se->tab_switch_count }}x Pindah Tab
                                </span>
                            @else
                                <span class="badge bg-light text-success border"><i class="fa-solid fa-shield-check me-1"></i> 0x Bersih</span>
                            @endif
                        </td>
                        <td class="fw-bold fs-6 {{ $se->is_passed ? 'text-success' : 'text-danger' }}">
                            {{ $se->status === 'submitted' ? $se->total_score : '-' }}
                        </td>
                        <td class="font-monospace text-muted small">{{ $se->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada siswa yang login masuk ke ujian ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
