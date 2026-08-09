@extends('layouts.app')

@section('title', 'Penilaian Projek P5: ' . $project->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('p5.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Projek
        </a>
        <h4 class="fw-bold mb-1">Lembar Penilaian Projek P5: {{ $project->title }}</h4>
        <p class="text-muted mb-0 small">Kelas: <strong>{{ $project->schoolClass?->name }}</strong> | Tema: <strong>{{ $project->theme }}</strong> | Skala: <strong>MB, SB, BSH, SAB</strong></p>
    </div>
</div>

<div class="card card-custom p-4 mb-4">
    <div class="p-3 bg-light rounded-3 border small">
        <strong class="text-dark d-block mb-1">Keterangan Skala Penilaian Profil Pelajar Pancasila:</strong>
        <div class="row g-2">
            <div class="col-md-3"><span class="badge bg-secondary me-1">MB</span> <strong>Mulai Berkembang:</strong> Siswa mulai memperlihatkan tanda-tanda awal.</div>
            <div class="col-md-3"><span class="badge bg-info text-dark me-1">SB</span> <strong>Sedang Berkembang:</strong> Siswa mulai menunjukkan kemampuan konsisten.</div>
            <div class="col-md-3"><span class="badge bg-primary me-1">BSH</span> <strong>Berkembang Sesuai Harapan:</strong> Siswa menunjukkan kemampuan optimal.</div>
            <div class="col-md-3"><span class="badge bg-success me-1">SAB</span> <strong>Sangat Berkembang:</strong> Siswa menunjukkan inisiatif dan kepemimpinan.</div>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <form action="{{ route('p5.scores.store', $project->id) }}" method="POST">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table-cells text-primary me-2"></i>Matriks Penilaian Dimensi Siswa:</h6>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                <i class="fa-solid fa-save me-1"></i> SIMPAN PENILAIAN P5
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">NO</th>
                        <th style="width: 140px;">NISN</th>
                        <th style="min-width: 180px;">NAMA LENGKAP SISWA</th>
                        @foreach($project->dimensions as $dim)
                            <th class="text-center" style="min-width: 160px;">
                                <div class="fw-bold text-primary">{{ $dim->dimension_name }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($dim->sub_element, 40) }}</div>
                            </th>
                        @endforeach
                        <th style="width: 110px;" class="text-center">AKSI RAPOR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $st)
                        <tr>
                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                            <td class="font-monospace text-muted">{{ $st->nisn }}</td>
                            <td class="fw-bold text-dark">{{ $st->name }}</td>
                            
                            @foreach($project->dimensions as $dim)
                                @php
                                    $savedScore = $dim->studentScores->firstWhere('student_id', $st->id)?->score ?? 'BSH';
                                @endphp
                                <td class="text-center">
                                    <select name="scores[{{ $dim->id }}][{{ $st->id }}]" class="form-select form-select-sm text-center fw-bold">
                                        <option value="MB" {{ $savedScore == 'MB' ? 'selected' : '' }}>MB</option>
                                        <option value="SB" {{ $savedScore == 'SB' ? 'selected' : '' }}>SB</option>
                                        <option value="BSH" {{ $savedScore == 'BSH' ? 'selected' : '' }}>BSH</option>
                                        <option value="SAB" {{ $savedScore == 'SAB' ? 'selected' : '' }}>SAB</option>
                                    </select>
                                </td>
                            @endforeach

                            <td class="text-center">
                                <a href="{{ route('p5.print', ['project' => $project->id, 'student' => $st->id]) }}" target="_blank" class="btn btn-xs btn-outline-secondary fw-bold" title="Cetak Rapor P5">
                                    <i class="fa-solid fa-print me-1"></i> Cetak P5
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $project->dimensions->count() }}" class="text-center text-muted py-4">Belum ada siswa di kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-save me-2"></i> SIMPAN SEMUA CAPAIAN PROJEK P5
            </button>
        </div>
    </form>
</div>
@endsection
