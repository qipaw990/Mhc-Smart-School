@extends('layouts.app')

@section('title', 'Master Ruangan & Lab')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Master Ruangan, Lab & Bengkel</h4>
        <p class="text-muted mb-0 small">Kelola kapasitas ruang kelas, laboratorium komputer, dan bengkel praktik SMK.</p>
    </div>
    <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addRoomModal">
        <i class="fa-solid fa-plus me-1"></i> Tambah Ruangan
    </button>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>KODE</th>
                    <th>NAMA RUANGAN</th>
                    <th>JENIS</th>
                    <th>KAPASITAS</th>
                    <th>LOKASI GEDUNG</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $r)
                    <tr>
                        <td class="fw-bold text-primary">{{ $r->code }}</td>
                        <td class="fw-semibold">{{ $r->name }}</td>
                        <td>
                            @if($r->type == 'lab')
                                <span class="badge bg-info bg-opacity-10 text-info"><i class="fa-solid fa-laptop-code me-1"></i> Lab Komputer</span>
                            @elseif($r->type == 'workshop')
                                <span class="badge bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-screwdriver-wrench me-1"></i> Bengkel Praktik</span>
                            @elseif($r->type == 'library')
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-book me-1"></i> Perpustakaan</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="fa-solid fa-door-open me-1"></i> Ruang Teori</span>
                            @endif
                        </td>
                        <td>{{ $r->capacity }} Siswa</td>
                        <td class="text-muted small">{{ $r->location ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $r->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-xs btn-outline-warning text-dark me-1" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $r->id }}">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <form action="{{ route('master.rooms.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ruangan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Room Modal -->
                    <div class="modal fade" id="editRoomModal{{ $r->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('master.rooms.update', $r->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Ruangan: {{ $r->code }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Kode Ruangan</label>
                                            <input type="text" name="code" class="form-control" value="{{ $r->code }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Ruangan</label>
                                            <input type="text" name="name" class="form-control" value="{{ $r->name }}" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Jenis Ruangan</label>
                                                <select name="type" class="form-select">
                                                    <option value="classroom" {{ $r->type == 'classroom' ? 'selected' : '' }}>Ruang Teori</option>
                                                    <option value="lab" {{ $r->type == 'lab' ? 'selected' : '' }}>Laboratorium</option>
                                                    <option value="workshop" {{ $r->type == 'workshop' ? 'selected' : '' }}>Bengkel Praktik</option>
                                                    <option value="library" {{ $r->type == 'library' ? 'selected' : '' }}>Perpustakaan</option>
                                                    <option value="hall" {{ $r->type == 'hall' ? 'selected' : '' }}>Aula / Hall</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Kapasitas</label>
                                                <input type="number" name="capacity" class="form-control" value="{{ $r->capacity }}" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Lokasi / Gedung</label>
                                            <input type="text" name="location" class="form-control" value="{{ $r->location }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" {{ $r->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="maintenance" {{ $r->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Room -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.rooms.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Ruangan / Lab Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Ruangan</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: LAB-RPL-1 / R101" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Ruangan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Lab Rekayasa Perangkat Lunak 1" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jenis Ruangan</label>
                            <select name="type" class="form-select">
                                <option value="classroom">Ruang Teori</option>
                                <option value="lab">Laboratorium</option>
                                <option value="workshop">Bengkel Praktik</option>
                                <option value="library">Perpustakaan</option>
                                <option value="hall">Aula / Hall</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kapasitas Siswa</label>
                            <input type="number" name="capacity" class="form-control" value="36" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lokasi Gedung / Lantai</label>
                        <input type="text" name="location" class="form-control" placeholder="Contoh: Gedung B Lantai 2">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Ruangan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
