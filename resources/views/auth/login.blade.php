<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Aplikasi - {{ $school->name ?? 'MHC SMART SCHOOL' }}</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #eaedf2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }

        .brand-section {
            background: #0f172a;
            color: white;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo-emblem {
            width: 72px;
            height: 72px;
            margin-bottom: 1.5rem;
        }

        .btn-kemendikbud {
            background-color: #0284c7;
            color: #ffffff;
            font-weight: 700;
            border-radius: 6px;
            padding: 0.65rem 1.2rem;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-kemendikbud:hover {
            background-color: #0369a1;
            color: #ffffff;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
            border-color: #0284c7;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="row g-0">
            <!-- Brand Section (Pusmendik / Kemendikbud Style) -->
            <div class="col-lg-5 brand-section d-none d-lg-flex">
                <div>
                    <!-- Logo Sekolah -->
                    @if($school && $school->logo && file_exists(public_path($school->logo)))
                        <img src="{{ asset($school->logo) }}" alt="Logo {{ $school->name }}" class="logo-emblem" style="width:72px;height:72px;object-fit:contain;background:#fff;border-radius:50%;padding:4px;">
                    @else
                        <svg class="logo-emblem" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="46" fill="#0284c7" stroke="#38bdf8" stroke-width="2"/>
                            <circle cx="50" cy="50" r="41" fill="#ffffff"/>
                            <path d="M50 18L60 38H40L50 18Z" fill="#f59e0b"/>
                            <path d="M30 45C30 35 70 35 70 45C70 65 50 78 50 78C50 78 30 65 30 45Z" fill="#0284c7" opacity="0.9"/>
                            <circle cx="50" cy="48" r="8" fill="#ffffff"/>
                            <path d="M38 72L50 82L62 72" stroke="#f59e0b" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    @endif

                    <div class="text-uppercase fw-extrabold text-info tracking-wider small mb-1">Pusat Asesmen &amp; Informasi Akademik</div>
                    <h3 class="fw-bold text-white mb-2">{{ strtoupper($school->name ?? 'MHC SMART SCHOOL') }}</h3>
                    <p class="text-light opacity-75 small">Sistem Informasi Manajemen Sekolah Terintegrasi Kurikulum Merdeka SMK dengan Prinsip <strong>ONE DATA SCHOOL</strong>.</p>
                </div>

                <div class="p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-10 mt-4">
                    <div class="fw-bold text-info small mb-1"><i class="fa-solid fa-shield-halved me-1"></i> Multi-Identifier Login</div>
                    <div class="small text-light opacity-75">Gunakan Username, Email, NISN (Siswa), atau NUPTK (Guru) untuk mengakses sistem secara aman.</div>
                </div>
            </div>

            <!-- Login Form Section -->
            <div class="col-lg-7 p-4 p-md-5">
                <div class="text-center text-lg-start mb-4">
                    <h4 class="fw-bold text-dark mb-1">Masuk ke Aplikasi 👋</h4>
                    <p class="text-muted small">Silakan masukkan kredensial akun Anda untuk melanjutkan.</p>
                </div>

                @if($errors->has('login'))
                    <div class="alert alert-danger border-0 small mb-3 py-2">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first('login') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Username / Email / NISN / NUPTK</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                            <input type="text" name="login" class="form-control bg-light border-start-0" placeholder="Contoh: admin / 0061234501 / 12345678..." value="{{ old('login') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="passwordInput" class="form-control bg-light border-start-0 border-end-0" placeholder="••••••••" required>
                            <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePassword()">
                                <i class="fa-regular fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label small text-secondary" for="rememberMe">Ingat Saya</label>
                        </div>
                        <a href="#" class="small text-decoration-none text-primary" onclick="alert('Silakan hubungi administrator Tata Usaha / Kurikulum untuk reset password.')">Lupa Password?</a>
                    </div>

                    <button type="submit" class="btn btn-kemendikbud w-100 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> MASUK KE SISTEM
                    </button>
                </form>

                <!-- Quick Demo Login Helpers -->
                <div class="mt-4 pt-3 border-top">
                    <div class="small fw-semibold text-secondary mb-2">Akun Demo (Klik untuk Isi Cepat):</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDemo('admin', 'password')">
                            👑 Super Admin
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDemo('198501152010011001', 'password')">
                            👨‍🏫 Guru / Wali Kelas
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDemo('0061234501', 'password')">
                            🎓 Siswa (NISN)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function setDemo(user, pass) {
            document.querySelector('input[name="login"]').value = user;
            document.querySelector('input[name="password"]').value = pass;
        }
    </script>
</body>
</html>
