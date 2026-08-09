@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@if($isTeacher && $teacher)
    {{-- ============================================ --}}
    {{-- TEACHER WORKSPACE DASHBOARD                 --}}
    {{-- ============================================ --}}

    {{-- 1. Teacher Identity Banner --}}
    <div class="card card-custom mb-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%); border-left: 3px solid var(--kem-primary) !important;">
        <div class="card-body" style="padding: 0.85rem 1rem;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="teacher-avatar">
                        {{ strtoupper(substr($teacher->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.96rem;">{{ $teacher->full_name }}</h6>
                            <span class="badge bg-primary">{{ $teacher->position ?? 'Guru Mata Pelajaran' }}</span>
                            <span class="badge bg-secondary">{{ $teacher->employment_status }}</span>
                            @if($teacherHomeroomClass)
                                <span class="badge bg-success"><i class="fa-solid fa-user-tie me-1"></i>Wali Kelas {{ $teacherHomeroomClass->name }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-3" style="font-size: 0.72rem; color: #64748b;">
                            <span><i class="fa-solid fa-id-card text-primary me-1"></i><strong>NUPTK:</strong> {{ $teacher->nuptk ?? $teacher->nip ?? '-' }}</span>
                            <span><i class="fa-regular fa-envelope text-secondary me-1"></i>{{ $teacher->email }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ route('attendance.index') }}" class="btn btn-primary btn-xs"><i class="fa-solid fa-qrcode me-1"></i>Presensi QR</a>
                    <a href="{{ route('journals.create') }}" class="btn btn-outline-primary btn-xs"><i class="fa-solid fa-pen-nib me-1"></i>Tulis Jurnal</a>
                    <a href="{{ route('gradebook.index') }}" class="btn btn-outline-success btn-xs"><i class="fa-solid fa-table-cells me-1"></i>Gradebook</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. KPI Metric Cards --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label">Beban Mengajar</div>
                <div class="kpi-value text-primary">{{ $teacherTotalJp }} <small style="font-size: 0.75rem; font-weight: 500;">JP/Mgg</small></div>
                <div class="kpi-sub">Target Standar: 24 JP</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label">Rombel Diampu</div>
                <div class="kpi-value text-success">{{ $teacherTeachingLoads->count() }} <small style="font-size: 0.75rem; font-weight: 500;">Kelas</small></div>
                <div class="kpi-sub">Kurikulum Merdeka</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label">Jurnal Mengajar</div>
                <div class="kpi-value text-info">{{ $teacherRecentJournals->count() }} <small style="font-size: 0.75rem; font-weight: 500;">Catatan</small></div>
                <div class="kpi-sub">Terverifikasi TP</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-label">Bank Soal CBT</div>
                <div class="kpi-value text-warning">{{ $teacherQuestionBanksCount }} <small style="font-size: 0.75rem; font-weight: 500;">Paket</small></div>
                <div class="kpi-sub">Siap Digunakan</div>
            </div>
        </div>
    </div>

    {{-- 3. Two-Column Workspace Layout --}}
    <div class="row g-3">
        {{-- Left Column (7 cols): Schedule & Journals --}}
        <div class="col-lg-7">

            {{-- Today's Schedule --}}
            <div class="card card-custom mb-3">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-calendar-day text-primary"></i>
                        <span>Jadwal Mengajar Hari Ini</span>
                    </div>
                    <span class="badge bg-light text-primary border">{{ date('l, d F Y') }}</span>
                </div>
                <div style="padding: 0.75rem 1rem;">
                    @if($teacherTodaySchedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">JAM</th>
                                        <th>MATA PELAJARAN</th>
                                        <th>ROMBEL</th>
                                        <th>RUANG</th>
                                        <th class="text-center" style="width: 110px;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teacherTodaySchedules as $s)
                                        <tr>
                                            <td class="fw-bold text-primary">Ke-{{ $s->period }}</td>
                                            <td><div class="fw-semibold">{{ $s->subject?->name ?? '-' }}</div></td>
                                            <td><span class="badge bg-light text-dark border">{{ $s->schoolClass?->name ?? '-' }}</span></td>
                                            <td><span class="badge bg-light text-secondary border">{{ $s->room?->name ?? 'Kelas' }}</span></td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ route('attendance.qr') }}" class="btn btn-xs btn-outline-primary" title="Presensi QR"><i class="fa-solid fa-qrcode me-1"></i>QR</a>
                                                    <a href="{{ route('journals.create') }}?class_id={{ $s->class_id }}&subject_id={{ $s->subject_id }}" class="btn btn-xs btn-outline-success" title="Tulis Jurnal"><i class="fa-solid fa-pen me-1"></i>Jurnal</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-3 text-center text-muted bg-light rounded" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-calendar-check text-success fs-4 d-block mb-1"></i>
                            <div class="fw-semibold mb-1">Tidak Ada Jadwal Hari Ini</div>
                            <span>Gunakan waktu untuk menyusun perangkat ajar atau menginput nilai di Gradebook.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Teaching Journals --}}
            <div class="card card-custom">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-book-open-reader text-info"></i>
                        <span>Jurnal Mengajar Terkini</span>
                    </div>
                    <a href="{{ route('journals.index') }}" class="btn btn-xs btn-outline-secondary">Lihat Semua</a>
                </div>
                <div style="padding: 0.75rem 1rem;">
                    @forelse($teacherRecentJournals as $j)
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2 p-2 rounded border bg-light">
                            <div>
                                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                    <span class="badge bg-primary" style="font-size: 0.63rem;">{{ $j->schoolClass?->name }}</span>
                                    <span class="fw-semibold text-dark" style="font-size: 0.78rem;">{{ $j->subject?->name }}</span>
                                    <span class="text-muted" style="font-size: 0.68rem;">• Ke-{{ $j->period_start }}-{{ $j->period_end }}</span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.71rem; line-height: 1.35;">
                                    <strong>Materi / TP:</strong> {{ Str::limit($j->learningObjective?->description ?? $j->notes ?? 'Penyampaian materi pembelajaran', 90) }}
                                </div>
                            </div>
                            <div class="text-nowrap text-muted flex-shrink-0" style="font-size: 0.67rem;">
                                {{ $j->date->format('d M Y') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-2 text-muted" style="font-size: 0.75rem;">
                            Belum ada jurnal mengajar yang diinput.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right Column (5 cols): Homeroom, SK PBM, Quick Actions --}}
        <div class="col-lg-5">

            {{-- Homeroom Class (only if Wali Kelas) --}}
            @if($teacherHomeroomClass)
                <div class="card card-custom mb-3" style="border-color: #22c55e !important;">
                    <div class="card-custom-header" style="background-color: #f0fdf4;">
                        <div class="fw-bold d-flex align-items-center gap-2 text-success" style="font-size: 0.82rem;">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>Kelas Binaan (Wali Kelas)</span>
                        </div>
                        <span class="badge bg-success">{{ $teacherHomeroomClass->name }}</span>
                    </div>
                    <div style="padding: 0.75rem 1rem;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted" style="font-size: 0.73rem;">Program Keahlian</span>
                            <span class="fw-semibold text-dark" style="font-size: 0.73rem;">{{ $teacherHomeroomClass->major?->name ?? 'RPL' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted" style="font-size: 0.73rem;">Jumlah Siswa</span>
                            <span class="fw-semibold text-primary" style="font-size: 0.73rem;">{{ $teacherHomeroomClass->students->count() }} Peserta Didik</span>
                        </div>
                        <a href="{{ route('rapor.index') }}" class="btn btn-success btn-xs w-100 fw-bold">
                            <i class="fa-solid fa-file-invoice me-1"></i>Buka E-Rapor Kelas {{ $teacherHomeroomClass->name }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Teaching Load SK PBM Table --}}
            <div class="card card-custom mb-3">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-list-check text-primary"></i>
                        <span>Beban Mengajar (SK PBM)</span>
                    </div>
                    <span class="badge bg-light text-primary border">{{ $teacherTotalJp }} JP Total</span>
                </div>
                <div style="padding: 0.75rem 1rem;">
                    @if($teacherTeachingLoads->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>MATA PELAJARAN</th>
                                        <th>KELAS</th>
                                        <th class="text-end">ALOKASI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teacherTeachingLoads as $load)
                                        <tr>
                                            <td class="fw-semibold">{{ $load->subject?->name ?? '-' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $load->schoolClass?->name ?? '-' }}</span></td>
                                            <td class="text-end"><span class="badge bg-primary">{{ $load->hours_per_week }} JP</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-2 text-muted" style="font-size: 0.74rem;">
                            Belum ada beban mengajar yang dialokasikan.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card card-custom">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-bolt text-warning"></i>
                        <span>Aksi Cepat Pendidik</span>
                    </div>
                </div>
                <div style="padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="{{ route('curriculum.modules.create') }}" class="quick-action-btn">
                        <span><i class="fa-solid fa-file-circle-plus text-primary me-2"></i>Susun Modul Ajar Baru</span>
                        <i class="fa-solid fa-chevron-right chevron"></i>
                    </a>
                    <a href="{{ route('gradebook.create') }}" class="quick-action-btn">
                        <span><i class="fa-solid fa-table-cells text-success me-2"></i>Input Nilai Asesmen KKTP</span>
                        <i class="fa-solid fa-chevron-right chevron"></i>
                    </a>
                    <a href="{{ route('cbt.banks.index') }}" class="quick-action-btn">
                        <span><i class="fa-solid fa-folder-tree text-warning me-2"></i>Kelola Bank Soal CBT</span>
                        <i class="fa-solid fa-chevron-right chevron"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

@else
    {{-- ============================================ --}}
    {{-- ADMIN / KEPALA SEKOLAH DASHBOARD            --}}
    {{-- ============================================ --}}
    <div class="row g-3">
        {{-- Left column --}}
        <div class="col-lg-8">
            {{-- Welcome / Announcement Card --}}
            <div class="card card-custom mb-3">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-bullhorn text-secondary"></i>
                        <span>Pengumuman</span>
                    </div>
                    <button onclick="location.reload()" class="btn-cyan-refresh">
                        <i class="fa-solid fa-rotate me-1"></i>Refresh
                    </button>
                </div>
                <div style="padding: 0.85rem 1rem;">
                    <div class="mb-2">
                        <span class="badge bg-success fw-bold" style="font-size: 0.7rem; border-radius: 3px;">
                            <i class="fa-solid fa-circle-check me-1"></i>Pengumuman Terakhir
                        </span>
                    </div>
                    <div class="p-3 bg-light rounded border d-flex gap-3 align-items-start">
                        <div style="min-width: 36px; height: 36px; border-radius: 50%; background-color: var(--kem-primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.9rem; flex-shrink: 0;" class="shadow-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                                <div>
                                    <div class="fw-bold text-primary" style="font-size: 0.86rem;">Selamat Datang di MHC Smart School Management System</div>
                                    <div class="text-muted fw-semibold" style="font-size: 0.69rem;">Pusat Asesmen & Tim Manajemen Kurikulum Merdeka</div>
                                </div>
                                <div class="text-muted d-flex align-items-center gap-3" style="font-size: 0.69rem; font-family: monospace;">
                                    <span><i class="fa-regular fa-calendar me-1"></i>{{ date('d-m-Y') }}</span>
                                    <span><i class="fa-regular fa-clock me-1"></i>{{ date('H:i') }}</span>
                                </div>
                            </div>
                            <hr class="my-2 opacity-25">
                            <div class="text-dark" style="line-height: 1.55; font-size: 0.77rem;">
                                <p class="mb-2">Aplikasi ini merupakan media manajemen dan informasi akademik terintegrasi <strong>(ONE DATA SCHOOL)</strong> untuk mengelola seluruh aktivitas pembelajaran, kurikulum, presensi, CBT, dan E-Rapor SMK Kurikulum Merdeka.</p>
                                <p class="mb-2">Seluruh data peserta didik yang tercatat di Master Data akan otomatis mengalir ke jadwal pelajaran, jurnal mengajar, gradebook asesmen, portal CBT anti-cheat, hingga penerbitan E-Rapor resmi tanpa duplikasi input data.</p>
                                <p class="mb-0 fw-semibold text-secondary">Salam Hormat, Tim Pengembang Sistem MHC Smart School.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI Summary Cards --}}
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Siswa</div>
                        <div class="kpi-value text-primary">{{ number_format($totalStudents) }}</div>
                        <div class="kpi-sub">Aktif Terdata</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Guru & Tendik</div>
                        <div class="kpi-value text-info">{{ number_format($totalTeachers) }}</div>
                        <div class="kpi-sub">Terdaftar Sistem</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Rombel Kelas</div>
                        <div class="kpi-value text-success">{{ number_format($totalClasses) }}</div>
                        <div class="kpi-sub">Fase E & Fase F</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-card">
                        <div class="kpi-label">Program Keahlian</div>
                        <div class="kpi-value text-warning">{{ number_format($totalMajors) }}</div>
                        <div class="kpi-sub">Kompetensi Aktif</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-lg-4">
            {{-- Helpdesk Card --}}
            <div class="card card-custom mb-3">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-headset text-secondary"></i>
                        <span>Posko Bantuan & Panduan</span>
                    </div>
                </div>
                <div style="padding: 0.75rem 1rem;">
                    <div class="posko-item">
                        <div class="posko-title"><i class="fa-solid fa-circle-question text-info me-1"></i> Layanan Bantuan Kurikulum Merdeka</div>
                        <div class="posko-detail">Hubungi Tim Helpdesk Kurikulum SMK & Asesmen Nasional.</div>
                    </div>
                    <div class="posko-item">
                        <div class="posko-title"><i class="fa-solid fa-phone text-success me-1"></i> WhatsApp Hotline</div>
                        <div class="posko-detail" style="font-family: monospace;">0812-3456-7890 &nbsp;(08:00–16:00 WIB)</div>
                    </div>
                    <div class="posko-item">
                        <div class="posko-title"><i class="fa-solid fa-envelope text-primary me-1"></i> Email Pusat Bantuan</div>
                        <div class="posko-detail" style="font-family: monospace;">helpdesk@mhcsmartschool.sch.id</div>
                    </div>
                </div>
            </div>

            {{-- Download Card --}}
            <div class="card card-custom">
                <div class="card-custom-header">
                    <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-download text-secondary"></i>
                        <span>Download Berkas & Panduan</span>
                    </div>
                </div>
                <div style="padding: 0.75rem 1rem;">
                    <div class="download-item">
                        <div>
                            <div class="download-title">Panduan Aplikasi Smart School SMK</div>
                            <div class="download-meta">PDF &bull; 2.4 MB &bull; Versi 2026</div>
                        </div>
                        <button class="download-btn" title="Unduh"><i class="fa-solid fa-arrow-down-to-bracket"></i></button>
                    </div>
                    <div class="download-item">
                        <div>
                            <div class="download-title">Template CP & ATP BSKAP 032/2024</div>
                            <div class="download-meta">DOCX &bull; 1.1 MB &bull; Resmi Kemendikbud</div>
                        </div>
                        <button class="download-btn" title="Unduh"><i class="fa-solid fa-arrow-down-to-bracket"></i></button>
                    </div>
                    <div class="download-item">
                        <div>
                            <div class="download-title">Format Nilai & Rubrik Projek P5</div>
                            <div class="download-meta">XLSX &bull; 850 KB &bull; Tim P5</div>
                        </div>
                        <button class="download-btn" title="Unduh"><i class="fa-solid fa-arrow-down-to-bracket"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
