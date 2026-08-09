@extends('layouts.app')

@section('title', 'Profil Sekolah & WhatsApp Gateway')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Profil Sekolah & WhatsApp Gateway</h4>
        <p class="text-muted mb-0 small">Kelola data resmi sekolah, jam operasional presensi, template WA, dan API Key WhatsApp Gateway.</p>
    </div>
    <div>
        <a href="{{ route('attendance.wa-logs') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-brands fa-whatsapp me-1"></i> Log WA Terkirim
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: School Profile, Attendance Times & WA Config -->
    <div class="col-lg-8">
        <!-- Card 1: Identitas Sekolah -->
        <div class="card card-custom p-4 mb-4">
            <form action="{{ route('master.school.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-school me-2"></i>Identitas Utama Sekolah</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Nama Resmi Sekolah</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $school->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Kode Sekolah</label>
                        <input type="text" class="form-control bg-light" value="{{ $school->school_code }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">NPSN</label>
                        <input type="text" name="npsn" class="form-control" value="{{ old('npsn', $school->npsn) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">NSS</label>
                        <input type="text" name="nss" class="form-control" value="{{ old('nss', $school->nss) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Akreditasi</label>
                        <select name="accreditation" class="form-select">
                            <option value="A" {{ $school->accreditation == 'A' ? 'selected' : '' }}>A (Unggul)</option>
                            <option value="B" {{ $school->accreditation == 'B' ? 'selected' : '' }}>B (Baik)</option>
                            <option value="C" {{ $school->accreditation == 'C' ? 'selected' : '' }}>C (Cukup)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nama Kepala Sekolah</label>
                        <input type="text" name="principal_name" class="form-control" value="{{ old('principal_name', $school->principal_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Upload Logo Sekolah (PNG / JPG, Max 2MB)</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-location-dot me-2"></i>Alamat & Kontak</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Alamat Jalan</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Kelurahan / Desa</label>
                        <input type="text" name="village" class="form-control" value="{{ old('village', $school->village) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Kecamatan</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district', $school->district) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Kabupaten / Kota</label>
                        <input type="text" name="regency" class="form-control" value="{{ old('regency', $school->regency) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Provinsi</label>
                        <input type="text" name="province" class="form-control" value="{{ old('province', $school->province) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Email Resmi</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Website</label>
                        <input type="text" name="website" class="form-control" value="{{ old('website', $school->website) }}">
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> SIMPAN PROFIL SEKOLAH
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Pengaturan Jam Masuk & Toleransi Keterlambatan Presensi -->
        <div class="card card-custom p-4 mb-4 border-start border-4 border-warning">
            <form action="{{ route('master.school.attendance-times') }}" method="POST">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-clock text-warning fs-5 me-2"></i>Jam Operasional Presensi & Toleransi Keterlambatan
                    </h6>
                    <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-3 py-1">Auto Status Evaluator</span>
                </div>

                <p class="text-muted small mb-3">Sistem otomatis mengubah status presensi dari HADIR menjadi TERLAMBAT (T) jika siswa melakukan scan melebihi jam batas keterlambatan.</p>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jam Masuk (Mulai Presensi)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-door-open text-muted"></i></span>
                            <input type="time" name="attendance_time_entry" class="form-control font-monospace" value="{{ old('attendance_time_entry', $timeEntry) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Batas Toleransi Keterlambatan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user-clock text-danger"></i></span>
                            <input type="time" name="attendance_time_late" class="form-control font-monospace text-danger fw-bold" value="{{ old('attendance_time_late', $timeLate) }}" required>
                        </div>
                        <div class="form-text small">Scan setelah jam ini = <strong>Terlambat (T)</strong></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jam Pulang / Selesai</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-door-closed text-muted"></i></span>
                            <input type="time" name="attendance_time_exit" class="form-control font-monospace" value="{{ old('attendance_time_exit', $timeExit) }}" required>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> SIMPAN JAM PRESENSI
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 3: Kustomisasi Template Pesan WhatsApp Presensi -->
        <div class="card card-custom p-4 mb-4 border-start border-4 border-success">
            <form action="{{ route('master.school.wa-template') }}" method="POST">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-success mb-0">
                        <i class="fa-brands fa-whatsapp fs-5 me-2"></i>Kustomisasi Template Pesan WhatsApp Presensi
                    </h6>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1">Dynamic Message Template</span>
                </div>

                <div class="alert alert-info small mb-3">
                    <div class="fw-bold mb-1"><i class="fa-solid fa-code me-1"></i> Ganti kata kunci di bawah sesuai kebutuhan (Sistem otomatis mengisi nilainya):</div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <code>{nama}</code>: Nama Siswa |
                        <code>{kelas}</code>: Nama Kelas |
                        <code>{status}</code>: Status Presensi (Hadir/Terlambat/Sakit) |
                        <code>{tanggal}</code>: Tanggal |
                        <code>{waktu}</code>: Jam |
                        <code>{sekolah}</code>: Nama Sekolah
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Isi Template Pesan WhatsApp Presensi</label>
                    <textarea name="wa_template_attendance" class="form-control font-monospace" rows="8" required>{{ old('wa_template_attendance', $waTemplate) }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> SIMPAN TEMPLATE WHATSAPP
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 4: Pengaturan API Key & URL WhatsApp Gateway -->
        <div class="card card-custom p-4 border-start border-4 border-secondary">
            <form action="{{ route('master.school.wa-settings') }}" method="POST">
                @csrf
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-key me-2"></i>Pengaturan Koneksi WA Gateway (API Key)
                    </h6>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-3 py-1">API Credentials</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Endpoint URL API Gateway</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-link text-muted"></i></span>
                            <input type="url" name="wa_gateway_url" class="form-control font-monospace" value="{{ old('wa_gateway_url', $waUrl) }}" required placeholder="https://api-gateway.smkmuthiaharapanclk.com">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Status WA Gateway</label>
                        <select name="wa_gateway_enabled" class="form-select">
                            <option value="1" {{ $waEnabled == '1' ? 'selected' : '' }}>🟢 Aktif (Kirim WA)</option>
                            <option value="0" {{ $waEnabled == '0' ? 'selected' : '' }}>🔴 Non-Aktif (Simulasi)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">API Key WhatsApp (Header: <code>X-API-KEY</code>)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="text" name="wa_gateway_key" class="form-control font-monospace" value="{{ old('wa_gateway_key', $waKey) }}" required placeholder="wag_68507eab...">
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-secondary px-4 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> SIMPAN KONEKSI API KEY
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Card Preview & Test Send WA -->
    <div class="col-lg-4">
        <!-- School Card Badge -->
        <div class="card card-custom p-4 text-center mb-4">
            <div class="bg-light p-3 rounded-3 d-inline-block mx-auto mb-3" style="min-width: 110px; min-height: 110px; display: flex; align-items: center; justify-content: center;">
                @if($school->logo && file_exists(public_path($school->logo)))
                    <img src="{{ asset($school->logo) }}" alt="Logo {{ $school->name }}" class="img-fluid" style="max-height: 100px; object-fit: contain;">
                @else
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Muthia Harapan" class="img-fluid" style="max-height: 100px; object-fit: contain;">
                @endif
            </div>
            <h5 class="fw-bold mb-1">{{ $school->name }}</h5>
            <div class="text-muted small mb-3">NPSN: {{ $school->npsn }} | Akreditasi: <span class="badge bg-success">{{ $school->accreditation }}</span></div>
            <div class="border-top pt-3 text-start small">
                <div class="mb-2"><strong>Kepala Sekolah:</strong> {{ $school->principal_name }}</div>
                <div class="mb-2"><strong>Email:</strong> {{ $school->email }}</div>
                <div class="mb-2"><strong>Telepon:</strong> {{ $school->phone }}</div>
                <div><strong>Alamat:</strong> {{ $school->address }}, {{ $school->district }}</div>
            </div>
        </div>

        <!-- Test Send WA Card -->
        <div class="card card-custom p-4 border-top border-4 border-info">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fa-paper-plane fa-solid text-info me-2"></i>Uji Coba Kirim WhatsApp
            </h6>
            <p class="text-muted small mb-3">Kirim pesan eksperimen langsung ke nomor HP untuk menguji koneksi API Gateway & template.</p>

            <form action="{{ route('master.school.wa-test') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Nomor WhatsApp Tujuan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                        <input type="text" name="phone" class="form-control" placeholder="Contoh: 08123456789" required value="{{ old('phone', $school->phone) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Nama Penerima (Opsional)</label>
                    <input type="text" name="recipient_name" class="form-control" placeholder="Contoh: Bpk. Ahmad" value="Penguji Uji Coba">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Isi Pesan Uji Coba</label>
                    <textarea name="message" class="form-control" rows="3" required>Halo dari SMK Muthia Harapan Cicalengka! Ini adalah pesan uji coba integrasi WhatsApp Gateway MHC Smart School.</textarea>
                </div>

                <button type="submit" class="btn btn-info text-white w-100 fw-bold">
                    <i class="fa-solid fa-paper-plane me-1"></i> KIRIM PESAN UJI COBA
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
