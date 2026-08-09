@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0.5">Manajemen Pengguna & Hak Akses (RBAC)</h5>
        <p class="text-muted mb-0" style="font-size: 0.76rem;">Kelola seluruh akun Administrator, Guru, Tata Usaha, dan Siswa terintegrasi dengan prinsip <strong>ONE DATA SCHOOL</strong>.</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna Baru
    </button>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card card-custom p-3 text-center">
            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Akun</div>
            <div class="fs-4 fw-bold text-primary mt-0.5">{{ number_format($totalUsers) }}</div>
            <div class="small text-muted" style="font-size: 0.7rem;">Terdaftar di Sistem</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-custom p-3 text-center">
            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Administrator</div>
            <div class="fs-4 fw-bold text-danger mt-0.5">{{ number_format($totalAdmins) }}</div>
            <div class="small text-muted" style="font-size: 0.7rem;">Akses Penuh & TU</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-custom p-3 text-center">
            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Tenaga Pendidik</div>
            <div class="fs-4 fw-bold text-info mt-0.5">{{ number_format($totalTeachers) }}</div>
            <div class="small text-muted" style="font-size: 0.7rem;">Guru & Wali Kelas</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-custom p-3 text-center">
            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Siswa Aktif</div>
            <div class="fs-4 fw-bold text-success mt-0.5">{{ number_format($totalStudents) }}</div>
            <div class="small text-muted" style="font-size: 0.7rem;">Login via NISN</div>
        </div>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card card-custom p-2.5 mb-3">
    <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-sm bg-light border-start-0" placeholder="Cari nama, username, atau email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role_id" class="form-select form-select-sm bg-light">
                <option value="">-- Semua Role Hak Akses --</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm bg-light">
                <option value="">-- Semua Status --</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 240px;">PENGGUNA</th>
                    <th style="width: 210px;">USERNAME & EMAIL</th>
                    <th style="width: 130px;">IDENTIFIER</th>
                    <th>ROLE HAK AKSES</th>
                    <th class="text-center" style="width: 90px;">STATUS</th>
                    <th style="width: 140px;">LOGIN TERAKHIR</th>
                    <th class="text-center" style="width: 120px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.78rem;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $u->name }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><i class="fa-solid fa-phone me-1"></i> {{ $u->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-monospace fw-semibold text-primary" style="font-size: 0.78rem;">{{ $u->username }}</div>
                            <div class="text-muted" style="font-size: 0.71rem;">{{ $u->email }}</div>
                        </td>
                        <td>
                            @if($u->teacher)
                                <span class="badge bg-light text-dark border font-monospace" title="NUPTK Guru">NUPTK: {{ $u->teacher->nuptk ?? $u->teacher->nip ?? '-' }}</span>
                            @elseif($u->student)
                                <span class="badge bg-light text-dark border font-monospace" title="NISN Siswa">NISN: {{ $u->student->nisn }}</span>
                            @else
                                <span class="badge bg-light text-secondary border">Akun Sistem</span>
                            @endif
                        </td>
                        <td>
                            @foreach($u->roles as $role)
                                <span class="badge bg-primary bg-opacity-10 text-primary mb-0.5" style="font-size: 0.7rem;">
                                    {{ $role->label }}
                                </span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $u->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size: 0.72rem;">
                            {{ $u->last_login_at ? $u->last_login_at->format('d M Y H:i') : 'Belum pernah' }}
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <!-- Reset Password Button -->
                                <button type="button" class="btn btn-xs btn-outline-info text-info" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $u->id }}" title="Reset Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                                <!-- Edit Button -->
                                <button type="button" class="btn btn-xs btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}" title="Edit Pengguna">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <!-- Delete Button -->
                                @if($u->id !== auth()->id() && $u->username !== 'admin')
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus / Nonaktifkan akun {{ $u->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit User -->
                    <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('users.update', $u->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">Edit Pengguna: {{ $u->name }}</div>
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $u->name }}" required>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control form-control-sm" value="{{ $u->username }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">No. Telepon / WA</label>
                                                <input type="text" name="phone" class="form-control form-control-sm" value="{{ $u->phone }}">
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Alamat Email</label>
                                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $u->email }}" required>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label">Role Hak Akses</label>
                                                <select name="role_id" class="form-select form-select-sm" required>
                                                    @foreach($roles as $r)
                                                        <option value="{{ $r->id }}" {{ $u->roles->contains('id', $r->id) ? 'selected' : '' }}>{{ $r->label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Status Akun</label>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="active" {{ $u->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="inactive" {{ $u->status === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Ganti Password (Opsional)</label>
                                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Kosongkan jika tidak diubah (min 6 karakter)">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-xs"><i class="fa-solid fa-save me-1"></i> Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Reset Password -->
                    <div class="modal fade" id="resetPasswordModal{{ $u->id }}" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <form action="{{ route('users.reset-password', $u->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;"><i class="fa-solid fa-key text-warning me-1"></i> Reset Password</div>
                                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="small text-muted mb-2">Reset kata sandi untuk akun <strong>{{ $u->username }}</strong> ({{ $u->name }}):</div>
                                        <div>
                                            <label class="form-label">Password Baru</label>
                                            <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Minimal 6 karakter" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-dark btn-xs fw-bold"><i class="fa-solid fa-check me-1"></i> Reset Sekarang</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-solid fa-users-slash text-muted mb-2 fs-3"></i>
                            <div class="fw-bold" style="font-size: 0.82rem;">Tidak Ada Pengguna Ditemukan</div>
                            <p class="small text-muted mb-0" style="font-size: 0.75rem;">Silakan sesuaikan filter pencarian atau tambahkan pengguna baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Tambah Pengguna Baru</div>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: Muhammad Farhan, S.Pd." required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="Contoh: farhan_guru" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control form-control-sm" placeholder="0812xxxxxxxx">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="farhan@smk.sch.id" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Role Hak Akses</label>
                            <select name="role_id" class="form-select form-select-sm" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ $r->name === 'guru' ? 'selected' : '' }}>{{ $r->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status Akun</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password Awal</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-xs"><i class="fa-solid fa-save me-1"></i> Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
