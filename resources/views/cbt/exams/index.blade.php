@extends('layouts.app')

@section('title', 'Jadwal & Proktor CBT')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0.5">Jadwal & Proktor Ujian CBT</h5>
        <p class="text-muted mb-0" style="font-size: 0.76rem;">Manajemen sesi pelaksanaan ujian online, token dinamis, dan <strong>Live Monitoring Proktor Anti-Cheat</strong>.</p>
    </div>
    <a href="{{ route('cbt.exams.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Buat Jadwal Ujian Baru
    </a>
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>NAMA UJIAN</th>
                    <th>MAPEL & GURU</th>
                    <th class="text-center" style="width: 110px;">TARGET ROMBEL</th>
                    <th class="text-center" style="width: 95px;">TOKEN</th>
                    <th style="width: 155px;">JADWAL & DURASI</th>
                    <th class="text-center" style="width: 125px;">STATUS</th>
                    <th class="text-center" style="width: 175px;">AKSI & MONITOR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $e)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $e->title }}</div>
                            <div class="text-muted" style="font-size: 0.71rem;">Bank: {{ $e->questionBank?->title }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">{{ $e->subject?->name }}</span>
                            <div class="text-muted mt-0.5" style="font-size: 0.71rem;"><i class="fa-solid fa-user-tie me-1"></i> {{ $e->teacher?->full_name }}</div>
                        </td>
                        <td class="text-center">
                            @foreach($e->examClasses as $ec)
                                <span class="badge bg-primary bg-opacity-10 text-primary mb-0.5">{{ $ec->schoolClass?->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger font-monospace px-2 py-0.5" style="font-size: 0.75rem; letter-spacing: 1px;">{{ $e->token }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark" style="font-size: 0.78rem;">{{ $e->start_time->format('d M Y H:i') }}</div>
                            <div class="text-muted" style="font-size: 0.71rem;"><i class="fa-regular fa-clock me-0.5"></i> {{ $e->duration_minutes }} Mnt | KKTP: {{ $e->kktp_score }}</div>
                        </td>
                        <td class="text-center">
                            @if($e->status === 'ongoing')
                                <span class="badge bg-success"><i class="fa-solid fa-circle-play me-1"></i> Berlangsung</span>
                            @elseif($e->status === 'published')
                                <span class="badge bg-info">Terjadwal</span>
                            @else
                                <span class="badge bg-secondary">Selesai</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('cbt.exams.monitor', $e->id) }}" class="btn btn-xs btn-outline-primary" title="Live Monitoring Proktor">
                                    <i class="fa-solid fa-desktop me-0.5"></i> Proktor
                                </a>
                                <a href="{{ route('cbt.exams.analytics', $e->id) }}" class="btn btn-xs btn-outline-info" title="Hasil & Analisis Nilai">
                                    <i class="fa-solid fa-chart-pie me-0.5"></i> Hasil
                                </a>
                                <form action="{{ route('cbt.exams.destroy', $e->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus jadwal ujian CBT ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-solid fa-laptop-code text-muted mb-2 fs-3"></i>
                            <div class="fw-bold" style="font-size: 0.82rem;">Belum Ada Jadwal Ujian CBT</div>
                            <p class="small text-muted mb-2" style="font-size: 0.75rem;">Jadwalkan ujian CBT baru dengan memilih bank soal yang telah dibuat.</p>
                            <a href="{{ route('cbt.exams.create') }}" class="btn btn-primary btn-xs">
                                <i class="fa-solid fa-plus me-1"></i> Jadwalkan Ujian Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($exams->hasPages())
        <div class="mt-3">
            {{ $exams->links() }}
        </div>
    @endif
</div>
@endsection
