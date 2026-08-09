@extends('layouts.app')

@section('title', 'Master Kelas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Master Rombel & Kelas</h4>
        <p class="text-muted mb-0 small">Kelola rombongan belajar tahun ajaran aktif: <strong>{{ $ay?->name ?? '2026/2027' }}</strong>.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.classes.template') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
        </a>
        <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importClassModal">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel/CSV
        </button>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="fa-solid fa-plus me-1"></i> Tambah Kelas Baru
        </button>
    </div>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>NAMA KELAS</th>
                    <th>TINGKAT</th>
                    <th>JURUSAN</th>
                    <th>WALI KELAS</th>
                    <th>RUANG KELAS</th>
                    <th>JUMLAH SISWA</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $c)
                    <tr>
                        <td class="fw-bold fs-6 text-primary">{{ $c->name }}</td>
                        <td><span class="badge bg-light text-dark border">Kelas {{ $c->grade_level }}</span></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $c->major?->code }}</span></td>
                        <td>
                            @if($c->homeroomTeacher)
                                <div class="fw-semibold small"><i class="fa-solid fa-user-tie text-muted me-1"></i> {{ $c->homeroomTeacher->full_name }}</div>
                            @else
                                <span class="text-muted small italic">Belum ditentukan</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $c->room?->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                {{ $c->students->count() }} / {{ $c->capacity }} Siswa
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button class="btn btn-xs btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editClassModal{{ $c->id }}" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="{{ route('master.classes.destroy', $c->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Class Modals -->
@foreach($classes as $c)
<div class="modal fade" id="editClassModal{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.classes.update', $c->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Kelas: {{ $c->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="name" class="form-control" value="{{ $c->name }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tingkat</label>
                            <select name="grade_level" class="form-select">
                                <option value="X" {{ $c->grade_level == 'X' ? 'selected' : '' }}>Kelas X</option>
                                <option value="XI" {{ $c->grade_level == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                <option value="XII" {{ $c->grade_level == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Program Keahlian</label>
                            <select name="major_id" class="form-select">
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}" {{ $c->major_id == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Wali Kelas</label>
                        <select name="homeroom_teacher_id" class="form-select">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ $c->homeroom_teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Ruang Homebase</label>
                            <select name="room_id" class="form-select">
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}" {{ $c->room_id == $r->id ? 'selected' : '' }}>{{ $r->code }} ({{ $r->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Kapasitas</label>
                            <input type="number" name="capacity" class="form-control" value="{{ $c->capacity }}" required>
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

<!-- Modal Add Class -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.classes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Rombel Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: X RPL 1" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tingkat</label>
                            <select name="grade_level" class="form-select">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Program Keahlian</label>
                            <select name="major_id" class="form-select" required>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}">{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Wali Kelas</label>
                        <select name="homeroom_teacher_id" class="form-select">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Ruang Homebase</label>
                            <select name="room_id" class="form-select">
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">{{ $r->code }} ({{ $r->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Kapasitas</label>
                            <input type="number" name="capacity" class="form-control" value="36" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Kelas -->
<div class="modal fade" id="importClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-import me-2 text-success"></i>Import Data Kelas (Excel/CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('master.classes.import') }}" method="POST" enctype="multipart/form-data">
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
                        <code>nama_kelas, tingkat, jurusan_kode, kapasitas</code><br>
                        <span class="text-secondary opacity-75">Tingkat: X, XI, XII</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('master.classes.template') }}" class="btn btn-outline-success btn-sm me-auto">
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
