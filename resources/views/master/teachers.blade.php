@extends('layouts.app')

@section('title', 'Master Guru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Master Data Guru & Tendik</h4>
        <p class="text-muted mb-0 small">Kelola data kepegawaian guru, NIP/NUPTK, akun login, dan penugasan jabatan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.teachers.template') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
        </a>
        <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importTeacherModal">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel/CSV
        </button>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Guru Baru
        </button>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('master.teachers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama guru, NIP, atau email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select bg-light">
                <option value="">-- Semua Status Kepegawaian --</option>
                <option value="PNS" {{ request('status') == 'PNS' ? 'selected' : '' }}>PNS</option>
                <option value="PPPK" {{ request('status') == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                <option value="GTY" {{ request('status') == 'GTY' ? 'selected' : '' }}>GTY (Guru Tetap Yayasan)</option>
                <option value="GTT" {{ request('status') == 'GTT' ? 'selected' : '' }}>GTT (Guru Tidak Tetap)</option>
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
                    <th>GURU</th>
                    <th>NIP / NUPTK</th>
                    <th>JENIS KELAMIN</th>
                    <th>STATUS KEPEGAWAIAN</th>
                    <th>JABATAN / PENUGASAN</th>
                    <th>AKUN LOGIN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                    {{ substr($t->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $t->full_name }}</div>
                                    <div class="text-muted small">{{ $t->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="font-monospace small">
                            <div class="fw-bold text-dark">{{ $t->nuptk ?? $t->nip ?? '-' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $t->nuptk ? 'NUPTK: ' . $t->nuptk : ($t->nip ? 'NIP: ' . $t->nip : '-') }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $t->gender == 'L' ? 'bg-info bg-opacity-10 text-info' : 'bg-danger bg-opacity-10 text-danger' }}">
                                {{ $t->gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $t->employment_status }}</span></td>
                        <td class="small">{{ $t->position ?? 'Guru Mata Pelajaran' }}</td>
                        <td>
                            @if($t->user)
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-circle-check me-1"></i> {{ $t->user->username }}</span>
                            @else
                                <span class="badge bg-secondary">No Account</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button class="btn btn-xs btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editTeacherModal{{ $t->id }}" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="{{ route('master.teachers.destroy', $t->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus data guru ini?')">
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
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data guru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($teachers->hasPages())
        <div class="mt-3">
            {{ $teachers->links() }}
        </div>
    @endif
</div>

<!-- Edit Teacher Modals -->
@foreach($teachers as $t)
<div class="modal fade" id="editTeacherModal{{ $t->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('master.teachers.update', $t->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Guru: {{ $t->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Gelar Depan</label>
                            <input type="text" name="title_prefix" class="form-control" value="{{ $t->title_prefix }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap (Tanpa Gelar)</label>
                            <input type="text" name="name" class="form-control" value="{{ $t->name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Gelar Belakang</label>
                            <input type="text" name="title_suffix" class="form-control" value="{{ $t->title_suffix }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NUPTK (Nomor Unik Pendidik)</label>
                            <input type="text" name="nuptk" class="form-control" value="{{ $t->nuptk }}" placeholder="16 digit NUPTK">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP (Opsional)</label>
                            <input type="text" name="nip" class="form-control" value="{{ $t->nip }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L" {{ $t->gender == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="P" {{ $t->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $t->email }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ $t->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Kepegawaian</label>
                            <select name="employment_status" class="form-select">
                                <option value="PNS" {{ $t->employment_status == 'PNS' ? 'selected' : '' }}>PNS</option>
                                <option value="PPPK" {{ $t->employment_status == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                <option value="GTY" {{ $t->employment_status == 'GTY' ? 'selected' : '' }}>GTY</option>
                                <option value="GTT" {{ $t->employment_status == 'GTT' ? 'selected' : '' }}>GTT</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Jabatan / Penugasan</label>
                            <input type="text" name="position" class="form-control" value="{{ $t->position }}">
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

<!-- Modal Add Teacher -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('master.teachers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Data Guru Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Gelar Depan</label>
                            <input type="text" name="title_prefix" class="form-control" placeholder="Dr. / Ir.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap (Tanpa Gelar)</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Ahmad Rizki" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Gelar Belakang</label>
                            <input type="text" name="title_suffix" class="form-control" placeholder="S.Pd., M.T.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NUPTK (Nomor Unik Pendidik)</label>
                            <input type="text" name="nuptk" class="form-control" placeholder="Contoh: 1234567890123456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">NIP (Opsional)</label>
                            <input type="text" name="nip" class="form-control" placeholder="Contoh: 19850115...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="guru@mhcsmartschool.sch.id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" class="form-control" placeholder="0812...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Kepegawaian</label>
                            <select name="employment_status" class="form-select">
                                <option value="PNS">PNS</option>
                                <option value="PPPK">PPPK</option>
                                <option value="GTY" selected>GTY (Guru Tetap Yayasan)</option>
                                <option value="GTT">GTT (Guru Tidak Tetap)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Jabatan / Penugasan</label>
                            <input type="text" name="position" class="form-control" placeholder="Guru Mapel / Kaprog">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="createAccountCheck" checked>
                        <label class="form-check-label small fw-semibold" for="createAccountCheck">
                            Buat Akun Login Pengguna secara otomatis (Username = NUPTK / Nama, Password default = <code>password</code>)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Guru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Guru -->
<div class="modal fade" id="importTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-import me-2 text-success"></i>Import Data Guru (Excel/CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('master.teachers.import') }}" method="POST" enctype="multipart/form-data">
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
                        <code>nama, nuptk, nip, jenis_kelamin, email, telepon, status_kepegawaian, jabatan</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('master.teachers.template') }}" class="btn btn-outline-success btn-sm me-auto">
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
