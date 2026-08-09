@extends('layouts.app')

@section('title', 'Input Nilai: ' . $assessment->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('gradebook.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Asesmen
        </a>
        <h4 class="fw-bold mb-1">{{ $assessment->title }}</h4>
        <p class="text-muted mb-0 small">
            Kelas: <strong>{{ $assessment->schoolClass?->name }}</strong> | 
            Mapel: <strong>{{ $assessment->subject?->name }}</strong> | 
            KKTP Minimal: <span class="badge bg-warning bg-opacity-10 text-warning fw-bold">{{ $assessment->kktp_score }}</span> | 
            Jenis: <span class="badge bg-primary bg-opacity-10 text-primary">{{ $assessment->type_label }}</span>
        </p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-primary border-4 text-center">
            <div class="text-muted small fw-semibold">RATA-RATA KELAS</div>
            <h3 class="fw-bold text-primary mb-0">{{ number_format($avgScore, 1) }}</h3>
            <div class="text-muted" style="font-size: 0.72rem;">Skor Maksimal: {{ $assessment->max_score }}</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-success border-4 text-center">
            <div class="text-muted small fw-semibold">TERCAPAI (LULUS KKTP)</div>
            <h3 class="fw-bold text-success mb-0">{{ $achievedCount }}</h3>
            <div class="text-muted" style="font-size: 0.72rem;">Siswa ({{ $scores->count() > 0 ? round(($achievedCount / $scores->count()) * 100) : 0 }}%)</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-danger border-4 text-center">
            <div class="text-muted small fw-semibold">BELUM TERCAPAI</div>
            <h3 class="fw-bold text-danger mb-0">{{ $notAchievedCount }}</h3>
            <div class="text-muted" style="font-size: 0.72rem;">Perlu Remedial</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card card-custom p-3 border-start border-warning border-4 text-center">
            <div class="text-muted small fw-semibold">REMEDIAL SELESAI</div>
            <h3 class="fw-bold text-warning mb-0">{{ $remedialCount }}</h3>
            <div class="text-muted" style="font-size: 0.72rem;">Siswa Diperbaiki</div>
        </div>
    </div>
</div>

<!-- Interactive Gradebook Table Matrix -->
<div class="card card-custom p-4">
    <form action="{{ route('gradebook.scores.store', $assessment->id) }}" method="POST">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table-cells text-primary me-2"></i>Matriks Lembar Nilai Siswa:</h6>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                <i class="fa-solid fa-save me-1"></i> SIMPAN SEMUA NILAI
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th style="width: 140px;">NISN</th>
                        <th>NAMA LENGKAP SISWA</th>
                        <th style="width: 120px;" class="text-center bg-primary bg-opacity-10 text-primary">NILAI ASLI</th>
                        <th style="width: 110px;" class="text-center">REMEDIAL?</th>
                        <th style="width: 130px;" class="text-center bg-warning bg-opacity-10 text-warning">NILAI REMEDIAL</th>
                        <th style="width: 120px;" class="text-center bg-success bg-opacity-10 text-success">NILAI AKHIR</th>
                        <th style="width: 140px;" class="text-center">STATUS KKTP</th>
                        <th>CATATAN GURU</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scores as $idx => $s)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-monospace text-muted">{{ $s->student?->nisn }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $s->student?->name }}</div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="scores[{{ $s->student_id }}][score]" class="form-control form-control-sm text-center fw-bold raw-score-input" value="{{ $s->score }}" min="0" max="100" required>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input remedial-check" type="checkbox" name="scores[{{ $s->student_id }}][is_remedial]" value="1" {{ $s->is_remedial ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="scores[{{ $s->student_id }}][remedial_score]" class="form-control form-control-sm text-center remedial-score-input" value="{{ $s->remedial_score }}" min="0" max="100" placeholder="Opsional">
                            </td>
                            <td class="text-center fw-bold fs-6 {{ $s->final_score >= $assessment->kktp_score ? 'text-success' : 'text-danger' }}">
                                {{ $s->final_score }}
                            </td>
                            <td class="text-center">
                                @if($s->achievement_status == 'achieved')
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Tercapai</span>
                                @else
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Perlu Remedial</span>
                                @endif
                            </td>
                            <td>
                                <input type="text" name="scores[{{ $s->student_id }}][teacher_notes]" class="form-control form-control-sm" value="{{ $s->teacher_notes }}" placeholder="Catatan capaian siswa...">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada siswa di rombel ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-save me-2"></i> SIMPAN SEMUA NILAI & KALKULASI KKTP
            </button>
        </div>
    </form>
</div>
@endsection
