@extends('layouts.app')

@section('title', 'Detail Siswa - ' . $student->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('master.students.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Siswa
        </a>
        <h4 class="fw-bold mb-1">Profil Lengkap Siswa</h4>
        <p class="text-muted mb-0 small">ONE DATA Hub untuk profil, histori kelas, akun orang tua, dan rekam akademik.</p>
    </div>
    <div>
        <span class="badge {{ $student->status == 'active' ? 'bg-success' : 'bg-secondary' }} fs-6 px-3 py-2">
            Status: {{ ucfirst($student->status) }}
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Student Bio & Parent Data -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                {{ substr($student->name, 0, 1) }}
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ $student->name }}</h5>
            <div class="text-muted small mb-3">NISN: {{ $student->nisn }} | NIS: {{ $student->nis ?? '-' }}</div>

            <div class="border-top pt-3 text-start small">
                <div class="mb-2"><strong>Kelas Sekarang:</strong> <span class="badge bg-light text-dark border">{{ $student->currentClass?->name ?? '-' }}</span></div>
                <div class="mb-2"><strong>Program Keahlian:</strong> {{ $student->major?->name }} ({{ $student->major?->code }})</div>
                <div class="mb-2"><strong>Jenis Kelamin:</strong> {{ $student->gender == 'L' ? 'Laki-Laki' : 'Perempuan' }}</div>
                <div class="mb-2"><strong>Tempat/Tgl Lahir:</strong> {{ $student->birth_place ?? '-' }}, {{ $student->birth_date?->format('d M Y') ?? '-' }}</div>
                <div class="mb-2"><strong>WhatsApp / HP:</strong> {{ $student->phone ?? '-' }}</div>
                <div class="mb-2"><strong>Email Akun:</strong> {{ $student->email }}</div>
                <div><strong>Alamat:</strong> {{ $student->address ?? '-' }}</div>
            </div>
        </div>

        <!-- Parent Info Card -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-users me-2"></i>Data Orang Tua / Wali</h6>
            @php $parent = $student->parents->first(); @endphp
            @if($parent)
                <div class="small">
                    <div class="mb-2"><strong>Nama Ayah:</strong> {{ $parent->father_name ?? '-' }}</div>
                    <div class="mb-2"><strong>No. WhatsApp Ayah:</strong> {{ $parent->father_phone ?? '-' }}</div>
                    <div class="mb-2"><strong>Nama Ibu:</strong> {{ $parent->mother_name ?? '-' }}</div>
                    <div class="mb-2"><strong>No. WhatsApp Ibu:</strong> {{ $parent->mother_phone ?? '-' }}</div>
                    <div class="mb-2"><strong>Alamat Orang Tua:</strong> {{ $parent->address ?? '-' }}</div>
                    @if($parent->user)
                        <div class="alert alert-light border small mt-3 mb-0">
                            <strong>Akun Portal Ortu:</strong> <code>{{ $parent->user->username }}</code>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-muted small">Belum ada data orang tua terkait.</div>
            @endif
        </div>
    </div>

    <!-- Right Column: Academic History & Modular Portals -->
    <div class="col-lg-8">
        <!-- Class Histories -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i>Histori Rombel & Kenaikan Kelas</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>TAHUN AJARAN</th>
                            <th>KELAS</th>
                            <th>AKSI / STATUS</th>
                            <th>TANGGAL DICATAT</th>
                            <th>CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->histories as $h)
                            <tr>
                                <td class="fw-bold">{{ $h->academicYear?->name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $h->schoolClass?->name ?? '-' }}</span></td>
                                <td>
                                    <span class="badge {{ $h->action == 'enrolled' ? 'bg-primary' : ($h->action == 'promoted' ? 'bg-success' : 'bg-secondary') }}">
                                        {{ ucfirst($h->action) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $h->created_at->format('d M Y') }}</td>
                                <td class="text-muted">{{ $h->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Belum ada histori kelas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Connected Modules Overview (ONE DATA HUB) -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-diagram-project me-2 text-primary"></i>ONE DATA Modular Linkages</h6>
            <p class="small text-muted mb-3">Status data siswa ini di seluruh modul terintegrasi:</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="fw-semibold small text-dark"><i class="fa-solid fa-clipboard-user text-success me-1"></i> Presensi Harian QR</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Terhubung langsung ke jadwal kelas {{ $student->currentClass?->name }}.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="fw-semibold small text-dark"><i class="fa-solid fa-chart-simple text-primary me-1"></i> Gradebook & Asesmen TP</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Nilai asesmen otomatis terkalkulasi ke e-Rapor.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="fw-semibold small text-dark"><i class="fa-solid fa-laptop-code text-info me-1"></i> CBT Exam System</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Token dan paket ujian langsung aktif sesuai tingkat {{ $student->currentClass?->grade_level }}.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="fw-semibold small text-dark"><i class="fa-solid fa-briefcase text-warning me-1"></i> PKL / Prakerin</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Penempatan industri dan jurnal harian SMK.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
