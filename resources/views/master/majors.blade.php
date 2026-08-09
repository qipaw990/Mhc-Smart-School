@extends('layouts.app')

@section('title', 'Master Jurusan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Master Jurusan & Program Keahlian</h4>
        <p class="text-muted mb-0 small">Kelola Program Keahlian / Jurusan SMK (RPL, TBSM, AKL, dll).</p>
    </div>
    <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addMajorModal">
        <i class="fa-solid fa-plus me-1"></i> Tambah Jurusan
    </button>
</div>

<div class="row g-3">
    @foreach($majors as $m)
        <div class="col-md-4">
            <div class="card card-custom p-4 h-100 position-relative">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 fw-bold">{{ $m->code }}</span>
                    <span class="badge {{ $m->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($m->status) }}
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $m->name }}</h5>
                <div class="small text-muted mb-3"><i class="fa-solid fa-user-tie me-1"></i> Kaprog: <strong>{{ $m->head_of_major ?? 'Belum ditentukan' }}</strong></div>
                <p class="small text-muted flex-grow-1">{{ $m->description ?? 'Tidak ada deskripsi.' }}</p>

                <div class="border-top pt-3 d-flex justify-content-between align-items-center small text-muted">
                    <span><i class="fa-solid fa-chalkboard-user me-1"></i> {{ $m->classes_count }} Rombel</span>
                    <span><i class="fa-solid fa-user-graduate me-1"></i> {{ $m->students_count }} Siswa</span>
                    <div>
                        <button class="btn btn-xs btn-outline-warning text-dark me-1" data-bs-toggle="modal" data-bs-target="#editMajorModal{{ $m->id }}">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Major Modal -->
        <div class="modal fade" id="editMajorModal{{ $m->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('master.majors.update', $m->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Jurusan: {{ $m->code }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kode Jurusan</label>
                                <input type="text" name="code" class="form-control" value="{{ $m->code }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Jurusan</label>
                                <input type="text" name="name" class="form-control" value="{{ $m->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kepala Program (Kaprog)</label>
                                <input type="text" name="head_of_major" class="form-control" value="{{ $m->head_of_major }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ $m->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $m->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $m->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
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
</div>

<!-- Modal Add Major -->
<div class="modal fade" id="addMajorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.majors.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Program Keahlian (Jurusan)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Jurusan (Singkatan)</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: RPL / TBSM / AKL" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap Jurusan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kepala Program (Kaprog)</label>
                        <input type="text" name="head_of_major" class="form-control" placeholder="Nama Guru Kaprog">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Jurusan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi kompetensi keahlian..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Jurusan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
