@extends('layouts.app')

@section('title', 'Gradebook & Asesmen Kurikulum Merdeka')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Gradebook & Asesmen Kurikulum Merdeka</h4>
        <p class="text-muted mb-0 small">Pengelolaan nilai <strong>Formatif, Sumatif Lingkup Materi (TP), dan Sumatif Akhir Semester</strong> dengan KKTP.</p>
    </div>
    <a href="{{ route('gradebook.create') }}" class="btn btn-primary btn-sm fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Buat Asesmen Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('gradebook.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-4">
            <select name="class_id" class="form-select bg-light">
                <option value="">-- Semua Rombel Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->major?->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select name="subject_id" class="form-select bg-light">
                <option value="">-- Semua Mata Pelajaran --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select bg-light">
                <option value="">-- Semua Jenis --</option>
                <option value="formative" {{ request('type') == 'formative' ? 'selected' : '' }}>Formatif</option>
                <option value="summative_tp" {{ request('type') == 'summative_tp' ? 'selected' : '' }}>Sumatif TP</option>
                <option value="summative_semester" {{ request('type') == 'summative_semester' ? 'selected' : '' }}>Sumatif SAS</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Assessments Table -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>JUDUL ASESMEN</th>
                    <th>JENIS ASESMEN</th>
                    <th>KELAS & MAPEL</th>
                    <th>TUJUAN PEMBELAJARAN (TP)</th>
                    <th>KKTP MINIMAL</th>
                    <th>TANGGAL</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $a)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $a->title }}</div>
                            <div class="text-muted small">Guru: {{ $a->teacher?->full_name }}</div>
                        </td>
                        <td>
                            @if($a->type == 'formative')
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold">Formatif</span>
                            @elseif($a->type == 'summative_tp')
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Sumatif TP</span>
                            @elseif($a->type == 'summative_semester')
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">Sumatif Akhir (SAS)</span>
                            @else
                                <span class="badge bg-secondary">Diagnostik</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $a->schoolClass?->name }}</span>
                            <div class="small text-muted">{{ $a->subject?->name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $a->learningObjective?->code ?? 'Semua TP' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning fw-bold fs-6">{{ $a->kktp_score }}</span>
                        </td>
                        <td class="text-muted small">{{ $a->date->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('gradebook.scores', $a->id) }}" class="btn btn-sm btn-primary fw-bold me-1">
                                <i class="fa-solid fa-table-cells me-1"></i> Input / Gradebook
                            </a>
                            <form action="{{ route('gradebook.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus asesmen ini beserta nilai seluruh siswa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fa-solid fa-award text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold">Belum Ada Asesmen yang Dibuat</h6>
                            <p class="small text-muted">Buat asesmen formatif atau sumatif untuk menginput nilai dan memantau Kriteria Ketercapaian TP (KKTP).</p>
                            <a href="{{ route('gradebook.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus me-1"></i> Buat Asesmen Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $assessments->links() }}
    </div>
</div>
@endsection
