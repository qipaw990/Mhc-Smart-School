@extends('layouts.app')

@section('title', 'Profil Saya & Keamanan')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Pengaturan Profil & Keamanan</h4>
            <p class="text-muted mb-0 small">Kelola informasi pribadi dan kata sandi akun Anda di MHC Smart School.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Success & Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-3 px-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close p-3" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-3 px-4 mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Terdapat kesalahan input:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close p-3" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Summary Card -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4 text-center">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow"
                         style="width: 84px; height: 84px; font-size: 2rem; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>

                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-2">{{ $user->email }}</div>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 flex-wrap">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold text-uppercase" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-shield-halved me-1"></i>{{ $role->name }}
                        </span>
                    @endforeach
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-circle-check me-1"></i>Aktif
                    </span>
                </div>

                <hr class="my-3">

                <div class="text-start small space-y-2">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted"><i class="fa-solid fa-user me-2"></i>Username</span>
                        <span class="fw-bold font-monospace">{{ $user->username }}</span>
                    </div>
                    @if($user->teacher)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="fa-solid fa-id-card me-2"></i>NIP / NUPTK</span>
                            <span class="fw-bold font-monospace">{{ $user->teacher->nip ?? $user->teacher->nuptk ?? '-' }}</span>
                        </div>
                    @endif
                    @if($user->student)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="fa-solid fa-id-card me-2"></i>NISN</span>
                            <span class="fw-bold font-monospace">{{ $user->student->nisn ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted"><i class="fa-solid fa-school me-2"></i>Kelas</span>
                            <span class="fw-bold">{{ $user->student->currentClass?->name ?? '-' }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted"><i class="fa-solid fa-calendar me-2"></i>Terdaftar</span>
                        <span class="fw-semibold">{{ $user->created_at?->isoFormat('D MMMM Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form Tabs / Cards -->
        <div class="col-lg-8">
            <!-- Card 1: Edit Informasi Profil -->
            <div class="card card-custom border-0 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="fa-solid fa-user-pen fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Informasi Profil</h6>
                        <span class="text-muted small">Perbarui data nama lengkap, email, dan nomor kontak Anda.</span>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username Login <span class="badge bg-light text-muted border ms-1">Tidak Dapat Diubah</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-at text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-light" value="{{ $user->username }}" readonly disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp / Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-brands fa-whatsapp text-success"></i></span>
                                <input type="text" name="phone" class="form-control border-start-0 @error('phone') is-invalid @enderror"
                                       placeholder="08123456789"
                                       value="{{ old('phone', $user->teacher?->phone ?? $user->student?->phone ?? '') }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-1.5"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Ganti Password -->
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                        <i class="fa-solid fa-key fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Ubah Kata Sandi (Password)</h6>
                        <span class="text-muted small">Pastikan kata sandi baru Anda minimal 8 karakter dan kombinasi unik.</span>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control border-start-0 border-end-0 @error('current_password') is-invalid @enderror" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('current_password', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" name="password" id="password"
                                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" placeholder="Min. 8 karakter" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control border-start-0 border-end-0" placeholder="Ketik ulang password baru" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password_confirmation', this)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-warning btn-sm px-4 fw-bold text-dark">
                            <i class="fa-solid fa-shield-halved me-1.5"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
