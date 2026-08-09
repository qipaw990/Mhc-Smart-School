@extends('layouts.app')

@section('title', 'Master Mata Pelajaran')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Struktur Kurikulum & Mata Pelajaran</h4>
        <p class="text-muted mb-0 small">Daftar mata pelajaran Kurikulum Merdeka (Muatan Umum, Dasar Kejuruan, Konsentrasi Keahlian, Mulok, P5).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('curriculum.subjects.template') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
        </a>
        <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importSubjectModal">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel/CSV
        </button>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="fa-solid fa-plus me-1"></i> Tambah Mata Pelajaran
        </button>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('curriculum.subjects.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama mapel atau kode..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="group" class="form-select bg-light">
                <option value="">-- Semua Kelompok --</option>
                <option value="A_general" {{ request('group') == 'A_general' ? 'selected' : '' }}>Muatan Umum (A)</option>
                <option value="B_vocational" {{ request('group') == 'B_vocational' ? 'selected' : '' }}>Dasar Kejuruan (B)</option>
                <option value="C_concentration" {{ request('group') == 'C_concentration' ? 'selected' : '' }}>Konsentrasi Keahlian (C)</option>
                <option value="mulok" {{ request('group') == 'mulok' ? 'selected' : '' }}>Muatan Lokal</option>
                <option value="p5" {{ request('group') == 'p5' ? 'selected' : '' }}>Projek P5</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="phase" class="form-select bg-light">
                <option value="">-- Semua Fase --</option>
                <option value="E" {{ request('phase') == 'E' ? 'selected' : '' }}>Fase E (Kelas X)</option>
                <option value="F" {{ request('phase') == 'F' ? 'selected' : '' }}>Fase F (Kelas XI/XII)</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>KODE</th>
                    <th>MATA PELAJARAN</th>
                    <th>KELOMPOK</th>
                    <th>FASE</th>
                    <th>JENIS</th>
                    <th>BEBAN JP / MINGGU</th>
                    <th>JURUSAN TERKAIT</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $s)
                    <tr>
                        <td class="fw-bold text-primary font-monospace">{{ $s->code }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $s->name }}</div>
                            <div class="text-muted small">{{ $s->learningOutcomes->count() }} Elemen Capaian Pembelajaran (CP)</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $s->group_label }}</span></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info fw-bold">Fase {{ $s->phase }}</span></td>
                        <td>
                            @if($s->type == 'practice')
                                <span class="badge bg-warning bg-opacity-10 text-warning">Praktik Lab/Bengkel</span>
                            @elseif($s->type == 'theory')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Teori Kelas</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">Teori & Praktik</span>
                            @endif
                        </td>
                        <td><strong>{{ $s->hours_per_week }} JP</strong> <span class="text-muted small">({{ $s->total_hours }} JP/Sem)</span></td>
                        <td><span class="badge bg-light text-dark border">{{ $s->major?->code ?? 'Semua Jurusan' }}</span></td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('curriculum.cp-tp.index', ['subject_id' => $s->id]) }}" class="btn btn-xs btn-outline-primary" title="Kelola CP & TP">
                                    <i class="fa-solid fa-bullseye me-0.5"></i> CP/TP
                                </a>
                                <button class="btn btn-xs btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editSubjectModal{{ $s->id }}" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="{{ route('curriculum.subjects.destroy', $s->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Data mata pelajaran belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subjects->withQueryString()->links() }}
    </div>
</div>

<!-- Edit Subject Modals -->
@foreach($subjects as $s)
<div class="modal fade" id="editSubjectModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('curriculum.subjects.update', $s->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Mata Pelajaran: {{ $s->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kode Mapel</label>
                            <input type="text" name="code" class="form-control" value="{{ $s->code }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Lengkap Mata Pelajaran</label>
                            <input type="text" name="name" class="form-control" value="{{ $s->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kelompok Kurikulum</label>
                            <select name="group" class="form-select">
                                <option value="A_general" {{ $s->group == 'A_general' ? 'selected' : '' }}>Muatan Umum (A)</option>
                                <option value="B_vocational" {{ $s->group == 'B_vocational' ? 'selected' : '' }}>Dasar Kejuruan (B)</option>
                                <option value="C_concentration" {{ $s->group == 'C_concentration' ? 'selected' : '' }}>Konsentrasi Keahlian (C)</option>
                                <option value="mulok" {{ $s->group == 'mulok' ? 'selected' : '' }}>Muatan Lokal</option>
                                <option value="p5" {{ $s->group == 'p5' ? 'selected' : '' }}>Projek P5</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Fase Kurikulum</label>
                            <select name="phase" class="form-select">
                                <option value="E" {{ $s->phase == 'E' ? 'selected' : '' }}>Fase E (Kelas X)</option>
                                <option value="F" {{ $s->phase == 'F' ? 'selected' : '' }}>Fase F (Kelas XI/XII)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Jenis Pelajaran</label>
                            <select name="type" class="form-select">
                                <option value="theory_practice" {{ $s->type == 'theory_practice' ? 'selected' : '' }}>Teori & Praktik</option>
                                <option value="practice" {{ $s->type == 'practice' ? 'selected' : '' }}>Praktik</option>
                                <option value="theory" {{ $s->type == 'theory' ? 'selected' : '' }}>Teori</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Beban JP per Minggu</label>
                            <input type="number" name="hours_per_week" class="form-control" value="{{ $s->hours_per_week }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Total JP per Semester</label>
                            <input type="number" name="total_hours" class="form-control" value="{{ $s->total_hours }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Program Keahlian (Jurusan)</label>
                            <select name="major_id" class="form-select">
                                <option value="">-- Semua Jurusan (Umum) --</option>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}" {{ $s->major_id == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $s->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $s->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Modal Add Subject -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('curriculum.subjects.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Mata Pelajaran Kurikulum Merdeka</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kode Mapel</label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: WEB-DEV / MTK" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Lengkap Mata Pelajaran</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Pemrograman Web & Perangkat Bergerak" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kelompok Kurikulum</label>
                            <select name="group" class="form-select">
                                <option value="A_general">Muatan Umum (A)</option>
                                <option value="B_vocational">Dasar Kejuruan (B)</option>
                                <option value="C_concentration" selected>Konsentrasi Keahlian (C)</option>
                                <option value="mulok">Muatan Lokal</option>
                                <option value="p5">Projek P5</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Fase Kurikulum</label>
                            <select name="phase" class="form-select">
                                <option value="E">Fase E (Kelas X)</option>
                                <option value="F" selected>Fase F (Kelas XI/XII)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Jenis Pelajaran</label>
                            <select name="type" class="form-select">
                                <option value="theory_practice" selected>Teori & Praktik</option>
                                <option value="practice">Praktik</option>
                                <option value="theory">Teori</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Beban JP per Minggu</label>
                            <input type="number" name="hours_per_week" class="form-control" value="6" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Total JP per Semester</label>
                            <input type="number" name="total_hours" class="form-control" value="108" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Program Keahlian</label>
                            <select name="major_id" class="form-select">
                                <option value="">-- Semua Jurusan (Umum) --</option>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}">{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Mata Pelajaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Mata Pelajaran -->
<div class="modal fade" id="importSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-import me-2 text-success"></i>Import Data Mata Pelajaran (Excel/CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('curriculum.subjects.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Gunakan format file <strong>.csv</strong> atau <strong>.xlsx</strong>. 
                        Unduh template untuk melihat contoh format kolom.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File CSV / Excel</label>
                        <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required>
                    </div>
                    <div class="text-muted small">
                        <strong>Urutan Kolom CSV:</strong><br>
                        <code>kode, nama, kelompok, fase, jam_per_minggu</code><br>
                        <span class="text-secondary opacity-75">Kelompok: A_general, B_vocational, C_concentration, mulok, p5 | Fase: E, F</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('curriculum.subjects.template') }}" class="btn btn-outline-success btn-sm me-auto">
                        <i class="fa-solid fa-download me-1"></i> Unduh Template CSV
                    </a>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Proses Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
