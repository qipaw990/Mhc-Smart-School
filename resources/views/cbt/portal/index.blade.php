@extends('layouts.app')

@section('title', 'Portal Ujian Siswa (CBT)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Portal Ujian CBT Siswa</h4>
        <p class="text-muted mb-0 small">Selamat datang, <strong>{{ $student?->name }}</strong> (NISN: {{ $student?->nisn }} | Kelas: {{ $student?->currentClass?->name }}).</p>
    </div>
</div>

<div class="row g-4">
    @forelse($activeExams as $exam)
        @php
            $attempt = $studentAttempts->get($exam->id);
        @endphp
        <div class="col-md-6">
            <div class="card card-custom p-4 h-100 shadow-sm border-2 {{ $attempt && $attempt->status === 'submitted' ? 'border-success' : 'border-primary' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $exam->subject?->name }}</span>
                    <span class="badge bg-light text-dark border"><i class="fa-regular fa-clock me-1 text-warning"></i> {{ $exam->duration_minutes }} Menit</span>
                </div>

                <h5 class="fw-bold text-dark mb-1">{{ $exam->title }}</h5>
                <div class="text-muted small mb-3">Guru: {{ $exam->teacher?->full_name }} | KKTP: <strong>{{ $exam->kktp_score }}</strong></div>

                <div class="p-3 bg-light rounded-3 border small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Jadwal Dibuka:</span>
                        <strong>{{ $exam->start_time->format('d M Y H:i') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Batas Pengumpulan:</span>
                        <strong>{{ $exam->end_time->format('d M Y H:i') }}</strong>
                    </div>
                </div>

                @if($attempt && $attempt->status === 'submitted')
                    <div class="alert alert-success border-0 py-2 small d-flex justify-content-between align-items-center mb-0 mt-auto">
                        <div>
                            <i class="fa-solid fa-circle-check me-1"></i> Ujian Selesai Dikumpulkan
                        </div>
                        <span class="fw-bold fs-6">Nilai: {{ $attempt->total_score }}</span>
                    </div>
                @elseif($attempt && $attempt->status === 'blocked')
                    <div class="alert alert-danger border-0 py-2 small mb-0 mt-auto">
                        <i class="fa-solid fa-ban me-1"></i> Ujian Terblokir oleh Pengawas (Pelanggaran Tab)
                    </div>
                @else
                    <button class="btn btn-primary w-100 fw-bold shadow-sm mt-auto" data-bs-toggle="modal" data-bs-target="#tokenModal{{ $exam->id }}">
                        <i class="fa-solid fa-key me-1"></i> MASUKKAN TOKEN & MULAI UJIAN
                    </button>
                @endif
            </div>
        </div>

        <!-- Token Input Modal -->
        <div class="modal fade" id="tokenModal{{ $exam->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('cbt.portal.start', $exam->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Konfirmasi Mulai Ujian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <i class="fa-solid fa-shield-halved text-primary fs-1 mb-2"></i>
                            <h5 class="fw-bold mb-1">{{ $exam->title }}</h5>
                            <p class="small text-muted mb-3">Durasi: {{ $exam->duration_minutes }} Menit | Anti-Cheat Mode Aktif</p>

                            <div class="col-md-8 mx-auto mb-3">
                                <label class="form-label small fw-semibold text-secondary">Masukkan Token Ujian dari Pengawas:</label>
                                <input type="text" name="token" class="form-control form-control-lg text-center text-uppercase font-monospace fw-bold letter-spacing-2" placeholder="Contoh: MERDEKA" required autofocus>
                            </div>

                            <div class="alert alert-warning border-0 small text-start mb-0">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Perhatian:</strong> Layar akan otomatis masuk ke mode Fullscreen. Dilarang berpindah tab browser selama ujian berlangsung.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">MULAI KERJAKAN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fa-solid fa-clipboard-check text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-bold">Tidak Ada Jadwal Ujian Aktif Hari Ini</h5>
            <p class="small text-muted">Silakan hubungi guru mata pelajaran untuk informasi jadwal ujian CBT.</p>
        </div>
    @endforelse
</div>
@endsection
