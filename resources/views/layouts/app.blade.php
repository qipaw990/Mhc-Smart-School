<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - MHC SMART SCHOOL</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --kem-primary: #0284c7;
            --kem-primary-dark: #0369a1;
            --kem-navy: #1e293b;
            --kem-sidebar-bg: #f8fafc;
            --kem-header-dark: #2d3e50;
            --kem-border: #e2e8f0;
            --kem-text-main: #334155;
            --kem-sidebar-width: 250px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #eaedf2;
            color: var(--kem-text-main);
            overflow-x: hidden;
            font-size: 0.8125rem; /* 13px Compact & Crisp */
            line-height: 1.45;
        }

        /* Compact Headings */
        h1 { font-size: 1.35rem; }
        h2 { font-size: 1.2rem; }
        h3 { font-size: 1.1rem; }
        h4 { font-size: 1.02rem; font-weight: 700; }
        h5 { font-size: 0.92rem; font-weight: 700; }
        h6 { font-size: 0.84rem; font-weight: 700; }

        .fs-6 { font-size: 0.8125rem !important; }
        .fs-5 { font-size: 0.9rem !important; }
        .fs-4 { font-size: 1.05rem !important; }
        .fs-3 { font-size: 1.18rem !important; }
        .fs-2 { font-size: 1.35rem !important; }

        /* 1. SIDEBAR THEME (KEMENDIKBUD / PUSMENDIK STYLE) */
        .sidebar {
            width: var(--kem-sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--kem-sidebar-bg);
            border-right: 1px solid var(--kem-border);
            z-index: 1040;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand-box {
            background-color: #ffffff;
            padding: 1rem 0.8rem 0.6rem;
            text-align: center;
            border-bottom: 1px solid var(--kem-border);
        }

        .sidebar-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            margin-bottom: 0.35rem;
        }

        .sidebar-title {
            font-size: 0.74rem;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            line-height: 1.2;
        }

        .sidebar-subtitle {
            font-size: 0.62rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 1px;
        }

        .sidebar-action-bar {
            background-color: var(--kem-header-dark);
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0.35rem 0.2rem;
            border-bottom: 1px solid rgba(0,0,0,0.15);
        }

        .sidebar-action-btn {
            color: #ffffff;
            font-size: 0.65rem;
            text-decoration: none;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            padding: 0.15rem 0.35rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .sidebar-action-btn i {
            font-size: 0.85rem;
        }

        .sidebar-action-btn:hover {
            color: #38bdf8;
            background: rgba(255,255,255,0.08);
        }

        .sidebar-menu {
            overflow-y: auto;
            flex-grow: 1;
            padding-bottom: 2rem;
        }

        .sidebar-header {
            padding: 0.5rem 0.85rem 0.3rem;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background-color: #f1f5f9;
            border-top: 1px solid var(--kem-border);
            border-bottom: 1px solid var(--kem-border);
        }

        .sidebar-item {
            padding: 0.48rem 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.8rem;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-item:hover {
            color: var(--kem-primary);
            background-color: #ffffff;
        }

        .sidebar-item.active {
            color: var(--kem-primary);
            background-color: #ffffff;
            border-left-color: var(--kem-primary);
            font-weight: 700;
        }

        .sidebar-item i {
            width: 16px;
            text-align: center;
            font-size: 0.88rem;
            color: #64748b;
        }

        .sidebar-item.active i {
            color: var(--kem-primary);
        }

        /* 2. TOPBAR */
        .topbar {
            height: 50px;
            margin-left: var(--kem-sidebar-width);
            background: #ffffff;
            border-bottom: 1px solid var(--kem-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.2rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        .topbar-toggle-btn {
            background: none;
            border: none;
            font-size: 1.15rem;
            color: #334155;
            cursor: pointer;
            padding: 0.2rem 0.4rem;
        }

        .topbar-badge-btn {
            position: relative;
            background: none;
            border: none;
            color: #64748b;
            font-size: 1rem;
            padding: 0.3rem;
            cursor: pointer;
        }

        .topbar-badge-btn .badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 0.58rem !important;
            padding: 0.15rem 0.3rem !important;
        }

        .user-avatar-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f97316;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* 3. CONTENT AREA & COMPACT UI OVERRIDES */
        .content-wrapper {
            margin-left: var(--kem-sidebar-width);
            padding: 1.2rem;
            min-height: calc(100vh - 50px);
            transition: all 0.3s ease;
        }

        .card-custom, .card {
            background: #ffffff;
            border: 1px solid var(--kem-border) !important;
            border-radius: 6px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
        }

        .card-custom-header, .card-header {
            padding: 0.65rem 1rem !important;
            background-color: #ffffff;
            border-bottom: 1px solid var(--kem-border) !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Compact Buttons */
        .btn {
            font-size: 0.78rem !important;
            padding: 0.3rem 0.65rem !important;
            line-height: 1.35 !important;
            border-radius: 5px !important;
            font-weight: 600 !important;
        }

        .btn-sm {
            font-size: 0.74rem !important;
            padding: 0.22rem 0.52rem !important;
            line-height: 1.25 !important;
            border-radius: 4px !important;
        }

        .btn-xs {
            font-size: 0.68rem !important;
            padding: 0.14rem 0.4rem !important;
            line-height: 1.2 !important;
            border-radius: 3px !important;
        }

        .btn-primary {
            background-color: var(--kem-primary) !important;
            border-color: var(--kem-primary) !important;
        }

        .btn-primary:hover {
            background-color: var(--kem-primary-dark) !important;
            border-color: var(--kem-primary-dark) !important;
        }

        .btn-outline-primary {
            color: var(--kem-primary) !important;
            border-color: var(--kem-primary) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--kem-primary) !important;
            color: #ffffff !important;
        }

        .btn-cyan-refresh {
            background-color: #00bcd4;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.74rem !important;
            padding: 0.22rem 0.65rem !important;
            border-radius: 4px !important;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .btn-cyan-refresh:hover {
            background-color: #00acc1;
            color: #ffffff;
        }

        /* Compact Table Design */
        .table thead th, .table-light th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            font-weight: 700 !important;
            font-size: 0.72rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            border-bottom: 1.5px solid #cbd5e1 !important;
            padding: 0.48rem 0.65rem !important;
            vertical-align: middle !important;
        }

        .table td {
            vertical-align: middle !important;
            padding: 0.48rem 0.65rem !important;
            font-size: 0.78rem !important;
            border-color: #e2e8f0 !important;
        }

        /* Compact Form Controls */
        .form-control, .form-select {
            border: 1px solid #cbd5e1 !important;
            border-radius: 5px !important;
            font-size: 0.78rem !important;
            padding: 0.32rem 0.6rem !important;
            min-height: auto !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--kem-primary) !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15) !important;
        }

        .form-label {
            font-size: 0.76rem !important;
            margin-bottom: 0.25rem !important;
            font-weight: 600 !important;
        }

        /* Compact Modals */
        .modal-content {
            border-radius: 6px !important;
            border: 1px solid var(--kem-border) !important;
        }

        .modal-header {
            background-color: #f8fafc;
            border-bottom: 1px solid var(--kem-border) !important;
            padding: 0.75rem 1rem !important;
        }

        .modal-body {
            padding: 1rem !important;
        }

        .modal-footer {
            background-color: #f8fafc;
            border-top: 1px solid var(--kem-border) !important;
            padding: 0.55rem 1rem !important;
        }

        /* Compact Badges */
        .badge {
            border-radius: 3px !important;
            font-size: 0.68rem !important;
            padding: 0.2rem 0.45rem !important;
            font-weight: 600 !important;
        }

        /* Pagination Styling & SVG Sizing Fix */
        .pagination {
            margin-bottom: 0 !important;
            font-size: 0.76rem !important;
            gap: 2px;
        }

        .pagination .page-link {
            padding: 0.22rem 0.55rem !important;
            color: var(--kem-primary) !important;
            border-color: #cbd5e1 !important;
            border-radius: 4px !important;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--kem-primary) !important;
            border-color: var(--kem-primary) !important;
            color: #ffffff !important;
        }

        nav svg, .pagination svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            vertical-align: middle;
        }

        /* ========================================= */
        /* DASHBOARD COMPONENT STYLES                */
        /* ========================================= */

        /* Teacher avatar circle */
        .teacher-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: var(--kem-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.15rem;
            box-shadow: 0 3px 8px rgba(2,132,199,0.25);
            flex-shrink: 0;
        }

        /* Helpdesk / Posko item list */
        .posko-item {
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .posko-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .posko-title {
            font-size: 0.76rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.15rem;
        }
        .posko-detail {
            font-size: 0.72rem;
            color: #64748b;
            line-height: 1.4;
        }

        /* Download item row */
        .download-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .download-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .download-title {
            font-size: 0.76rem;
            font-weight: 600;
            color: #334155;
            line-height: 1.3;
            margin-bottom: 0.1rem;
        }
        .download-meta {
            font-size: 0.68rem;
            color: #94a3b8;
        }
        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 5px;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: var(--kem-primary);
            font-size: 0.72rem;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .download-btn:hover {
            background-color: var(--kem-primary);
            color: #ffffff;
            border-color: var(--kem-primary);
        }

        /* Quick action button row */
        .quick-action-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.55rem 0.75rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            text-decoration: none;
            color: #334155;
            font-size: 0.76rem;
            font-weight: 500;
            transition: all 0.18s;
        }
        .quick-action-btn:hover {
            background-color: #e0f2fe;
            border-color: var(--kem-primary);
            color: var(--kem-primary);
        }
        .quick-action-btn .chevron {
            font-size: 0.6rem;
            color: #94a3b8;
        }

        /* KPI metric card */
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--kem-border);
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            padding: 0.75rem 0.65rem;
            text-align: center;
        }
        .kpi-label {
            font-size: 0.63rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }
        .kpi-value {
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.2rem;
        }
        .kpi-sub {
            font-size: 0.66rem;
            color: #94a3b8;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .topbar, .content-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    @php 
        $user = Auth::user(); 
        $isSuperOrAdmin = $user && $user->hasRole(['super_admin', 'admin_sekolah', 'kepala_sekolah', 'wakasek_kurikulum', 'tata_usaha']);
        $isGuruOnly = $user && $user->hasRole('guru') && !$isSuperOrAdmin;
        $isHomeroomTeacher = $user?->teacher && $user->teacher->homeroomClasses->isNotEmpty();
        $currentSchool = \App\Models\School::first();
    @endphp

    <!-- Sidebar (Pusmendik / ANBK Style) -->
    <aside class="sidebar" id="appSidebar">
        <!-- Logo & Title -->
        <div class="sidebar-brand-box">
            @if($currentSchool && $currentSchool->logo && file_exists(public_path($currentSchool->logo)))
                <img src="{{ asset($currentSchool->logo) }}" class="sidebar-logo mb-1" style="max-height: 48px; width: auto; object-fit: contain;" alt="Logo {{ $currentSchool->name }}">
            @else
                <svg class="sidebar-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="46" fill="#0284c7" stroke="#0369a1" stroke-width="2"/>
                    <circle cx="50" cy="50" r="41" fill="#ffffff"/>
                    <path d="M50 18L60 38H40L50 18Z" fill="#f59e0b"/>
                    <path d="M30 45C30 35 70 35 70 45C70 65 50 78 50 78C50 78 30 65 30 45Z" fill="#0284c7" opacity="0.9"/>
                    <circle cx="50" cy="48" r="8" fill="#ffffff"/>
                    <path d="M38 72L50 82L62 72" stroke="#f59e0b" stroke-width="3" stroke-linecap="round"/>
                </svg>
            @endif
            <div class="sidebar-title">{{ strtoupper($currentSchool->name ?? 'MHC SMART SCHOOL') }}</div>
            <div class="sidebar-subtitle">PUSAT MANAJEMEN & INFORMASI AKADEMIK</div>
        </div>

        <!-- 3-Action Bar Underneath Logo -->
        <div class="sidebar-action-bar">
            <a href="{{ route('dashboard') }}" class="sidebar-action-btn" title="Dashboard Utama">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
            @if($isSuperOrAdmin)
                <a href="{{ route('master.school.index') }}" class="sidebar-action-btn" title="Pengaturan Sistem">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Pengaturan</span>
                </a>
            @endif
            <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="sidebar-action-btn bg-transparent border-0" title="Keluar dari Aplikasi">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

        <!-- Menu Links -->
        <div class="sidebar-menu">
            <div class="sidebar-header">MENU UTAMA</div>
            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>

            @if($isSuperOrAdmin)
                <!-- ================= ADMIN NAVIGATION ================= -->
                <div class="sidebar-header">DATA MASTER</div>
                
                <a href="{{ route('master.school.index') }}" class="sidebar-item {{ request()->routeIs('master.school.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-building"></i>
                    <span>Profil Sekolah</span>
                </a>

                <a href="{{ route('master.academic-year.index') }}" class="sidebar-item {{ request()->routeIs('master.academic-year.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar-check"></i>
                    <span>Tahun Ajaran & Semester</span>
                </a>

                <a href="{{ route('master.majors.index') }}" class="sidebar-item {{ request()->routeIs('master.majors.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>Master Program Keahlian</span>
                </a>

                <a href="{{ route('master.rooms.index') }}" class="sidebar-item {{ request()->routeIs('master.rooms.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-door-closed"></i>
                    <span>Master Ruang & Lab</span>
                </a>

                <a href="{{ route('master.classes.index') }}" class="sidebar-item {{ request()->routeIs('master.classes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-rectangle"></i>
                    <span>Master Rombel Kelas</span>
                </a>

                <a href="{{ route('master.teachers.index') }}" class="sidebar-item {{ request()->routeIs('master.teachers.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>Master Tenaga Pendidik</span>
                </a>

                <a href="{{ route('master.students.index') }}" class="sidebar-item {{ request()->routeIs('master.students.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Master Peserta Didik</span>
                </a>

                <div class="sidebar-header">KURIKULUM & JADWAL</div>
                
                <a href="{{ route('curriculum.subjects.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.subjects.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Mata Pelajaran</span>
                </a>

                <a href="{{ route('curriculum.cp-tp.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.cp-tp.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-crosshairs"></i>
                    <span>Capaian (CP) & TP</span>
                </a>

                <a href="{{ route('curriculum.atp.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.atp.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-timeline"></i>
                    <span>Alur Tujuan (ATP)</span>
                </a>

                <a href="{{ route('curriculum.materials.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.materials.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-file-lines"></i>
                    <span>Materi & Modul Ajar</span>
                </a>

                <a href="{{ route('scheduler.index') }}" class="sidebar-item {{ request()->routeIs('scheduler.index') ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span>Jadwal Pelajaran Otomatis</span>
                </a>

                <a href="{{ route('scheduler.loads') }}" class="sidebar-item {{ request()->routeIs('scheduler.loads') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Beban Mengajar (SK PBM)</span>
                </a>

                <a href="{{ route('scheduler.conflicts') }}" class="sidebar-item {{ request()->routeIs('scheduler.conflicts') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Detektor Konflik Jadwal</span>
                </a>

                <div class="sidebar-header">PRESENSI & ASESMEN</div>

                <a href="{{ route('attendance.index') }}" class="sidebar-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Presensi Smart QR</span>
                </a>

                <a href="{{ route('attendance.wa-logs') }}" class="sidebar-item {{ request()->routeIs('attendance.wa-logs') ? 'active' : '' }}">
                    <i class="fa-brands fa-whatsapp text-success"></i>
                    <span>Log WA Presensi</span>
                </a>

                <a href="{{ route('journals.index') }}" class="sidebar-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open-reader"></i>
                    <span>Jurnal Mengajar Guru</span>
                </a>

                <a href="{{ route('gradebook.index') }}" class="sidebar-item {{ request()->routeIs('gradebook.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Gradebook & KKTP</span>
                </a>

                <div class="sidebar-header">CBT EXAM ENGINE</div>

                <a href="{{ route('cbt.banks.index') }}" class="sidebar-item {{ request()->routeIs('cbt.banks.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    <span>Bank Soal (Multi-Format)</span>
                </a>

                <a href="{{ route('cbt.exams.index') }}" class="sidebar-item {{ request()->routeIs('cbt.exams.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-desktop"></i>
                    <span>Jadwal & Proktor CBT</span>
                </a>

                <div class="sidebar-header">E-RAPOR & PROJEK P5</div>

                <a href="{{ route('rapor.index') }}" class="sidebar-item {{ request()->routeIs('rapor.index') || request()->routeIs('rapor.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>E-Rapor Akademik</span>
                </a>

                <a href="{{ route('rapor.leger') }}" class="sidebar-item {{ request()->routeIs('rapor.leger') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Leger Nilai Semester</span>
                </a>

                <a href="{{ route('p5.index') }}" class="sidebar-item {{ request()->routeIs('p5.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hands-holding-child"></i>
                    <span>Rapor Projek P5</span>
                </a>

                <div class="sidebar-header">MANAJEMEN PENGGUNA</div>

                <a href="{{ route('users.index') }}" class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Pengguna & Hak Akses</span>
                </a>

            @elseif($isGuruOnly)
                <!-- ================= TEACHER SPECIFIC NAVIGATION ================= -->
                <div class="sidebar-header">PERANGKAT & JADWAL SAYA</div>

                <a href="{{ route('scheduler.index') }}" class="sidebar-item {{ request()->routeIs('scheduler.index') ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span>Jadwal Mengajar Saya</span>
                </a>

                <a href="{{ route('curriculum.cp-tp.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.cp-tp.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-crosshairs"></i>
                    <span>Capaian (CP) & TP</span>
                </a>

                <a href="{{ route('curriculum.atp.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.atp.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-timeline"></i>
                    <span>Alur Tujuan (ATP)</span>
                </a>

                <a href="{{ route('curriculum.materials.index') }}" class="sidebar-item {{ request()->routeIs('curriculum.materials.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-file-lines"></i>
                    <span>Materi & Modul Ajar</span>
                </a>

                <div class="sidebar-header">AKTIVITAS PEMBELAJARAN</div>

                <a href="{{ route('attendance.index') }}" class="sidebar-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Presensi Smart QR</span>
                </a>

                <a href="{{ route('journals.index') }}" class="sidebar-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open-reader"></i>
                    <span>Jurnal Mengajar Saya</span>
                </a>

                <a href="{{ route('gradebook.index') }}" class="sidebar-item {{ request()->routeIs('gradebook.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Gradebook & Nilai KKTP</span>
                </a>

                <div class="sidebar-header">CBT & ASESMEN SISWA</div>

                <a href="{{ route('cbt.banks.index') }}" class="sidebar-item {{ request()->routeIs('cbt.banks.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-tree"></i>
                    <span>Bank Soal Saya</span>
                </a>

                <a href="{{ route('cbt.exams.index') }}" class="sidebar-item {{ request()->routeIs('cbt.exams.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-desktop"></i>
                    <span>Jadwal & Proktor CBT</span>
                </a>

                @if($isHomeroomTeacher)
                    <div class="sidebar-header">WALI KELAS & RAPOR</div>

                    <a href="{{ route('rapor.index') }}" class="sidebar-item {{ request()->routeIs('rapor.index') || request()->routeIs('rapor.show') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>E-Rapor Kelas Binaan</span>
                    </a>

                    <a href="{{ route('rapor.leger') }}" class="sidebar-item {{ request()->routeIs('rapor.leger') ? 'active' : '' }}">
                        <i class="fa-solid fa-table-list"></i>
                        <span>Leger Nilai Semester</span>
                    </a>

                    <a href="{{ route('p5.index') }}" class="sidebar-item {{ request()->routeIs('p5.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-hands-holding-child"></i>
                        <span>Rapor Projek P5</span>
                    </a>
                @endif

            @elseif($user && $user->hasRole(['siswa', 'student']))
                <!-- ================= STUDENT NAVIGATION ================= -->
                <div class="sidebar-header">AKADEMIK SAYA</div>

                <a href="{{ route('student.nilai') }}" class="sidebar-item {{ request()->routeIs('student.nilai') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-bar"></i>
                    <span>Nilai & Asesmen Saya</span>
                </a>

                <a href="{{ route('student.kehadiran') }}" class="sidebar-item {{ request()->routeIs('student.kehadiran') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Rekap Kehadiran Saya</span>
                </a>

                <div class="sidebar-header">ASESMEN & UJIAN</div>

                <a href="{{ route('cbt.portal.index') }}" class="sidebar-item {{ request()->routeIs('cbt.portal.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-pen"></i>
                    <span>Portal Ujian CBT</span>
                </a>
            @endif
        </div>
    </aside>

    <!-- Topbar Header -->
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="topbar-toggle-btn" id="sidebarToggleBtn" onclick="document.getElementById('appSidebar').classList.toggle('show')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="d-none d-md-block small text-secondary fw-semibold">
                Sistem Informasi Manajemen Sekolah Terintegrasi (Kurikulum Merdeka SMK)
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button class="topbar-badge-btn" title="Pengguna Online">
                <i class="fa-solid fa-user-group"></i>
                <span class="badge bg-success rounded-pill">0</span>
            </button>

            <button class="topbar-badge-btn" title="Notifikasi Sistem">
                <i class="fa-regular fa-bell"></i>
                <span class="badge bg-danger rounded-pill">0</span>
            </button>

            <!-- User Avatar & Dropdown -->
            <div class="dropdown">
                <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="font-size: 0.8rem;">
                    <li class="px-3 py-1.5 border-bottom">
                        <div class="fw-bold text-dark">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ Auth::user()->primary_role ?? 'Pengguna' }}</div>
                    </li>
                    <li><a class="dropdown-item py-1.5" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge-high me-2 text-primary"></i> Dashboard</a></li>
                    @if($isSuperOrAdmin)
                        <li><a class="dropdown-item py-1.5" href="{{ route('master.school.index') }}"><i class="fa-solid fa-gear me-2 text-secondary"></i> Pengaturan Sekolah & Sistem</a></li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-1.5 text-danger">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="content-wrapper">
        <!-- Flash Message Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" role="alert" style="font-size: 0.8rem;">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-sm p-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm py-2 px-3 mb-3" role="alert" style="font-size: 0.8rem;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-sm p-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>
