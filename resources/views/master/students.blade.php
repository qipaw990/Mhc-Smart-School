@extends('layouts.app')

@section('title', 'Master Siswa')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Master Data Siswa</h4>
        <p class="text-muted mb-0 small">Prinsip <strong>ONE DATA SCHOOL</strong>: Data siswa terintegrasi ke Kelas, Presensi, Nilai, CBT, Rapor, BK, dan PKL.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.students.id-cards', ['class_id' => request('class_id')]) }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="fa-solid fa-address-card me-1"></i> Cetak Kartu Pelajar
        </a>
        <a href="{{ route('master.students.template') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i> Unduh Template
        </a>
        <button class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importStudentModal">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel/CSV
        </button>
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Siswa Baru
        </button>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('master.students.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama siswa, NISN, atau NIS..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="class_id" class="form-select bg-light">
                <option value="">-- Semua Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="major_id" class="form-select bg-light">
                <option value="">-- Semua Jurusan --</option>
                @foreach($majors as $m)
                    <option value="{{ $m->id }}" {{ request('major_id') == $m->id ? 'selected' : '' }}>{{ $m->code }}</option>
                @endforeach
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
                    <th>SISWA</th>
                    <th>NISN / NIS</th>
                    <th>KELAS</th>
                    <th>JURUSAN</th>
                    <th>JENIS KELAMIN</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                    {{ substr($s->name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('master.students.show', $s->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">{{ $s->name }}</a>
                                    <div class="text-muted small">{{ $s->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="font-monospace small">
                            <div>{{ $s->nisn }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">NIS: {{ $s->nis ?? '-' }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $s->currentClass?->name ?? 'Belum ada kelas' }}</span></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $s->major?->code ?? '-' }}</span></td>
                        <td>
                            <span class="badge {{ $s->gender == 'L' ? 'bg-info bg-opacity-10 text-info' : 'bg-danger bg-opacity-10 text-danger' }}">
                                {{ $s->gender == 'L' ? 'L' : 'P' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $s->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('master.students.id-card', $s->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Cetak Kartu Pelajar">
                                    <i class="fa-solid fa-address-card"></i>
                                </a>
                                <a href="{{ route('master.students.show', $s->id) }}" class="btn btn-xs btn-outline-info text-info" title="Lihat Profil">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button class="btn btn-xs btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editStudentModal{{ $s->id }}" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                                <form action="{{ route('master.students.destroy', $s->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus data siswa ini?')">
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
                        <td colspan="7" class="text-center text-muted py-4">Data siswa tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->withQueryString()->links() }}
    </div>
</div>

<!-- Edit Student Modals -->
@foreach($students as $s)
<div class="modal fade" id="editStudentModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('master.students.update', $s->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Data Siswa: {{ $s->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ $s->name }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">NISN</label>
                            <input type="text" name="nisn" class="form-control" value="{{ $s->nisn }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">NIS</label>
                            <input type="text" name="nis" class="form-control" value="{{ $s->nis }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kelas Sekarang</label>
                            <select name="current_class_id" class="form-select" required>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ $s->current_class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Keahlian (Jurusan)</label>
                            <select name="major_id" class="form-select" required>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}" {{ $s->major_id == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L" {{ $s->gender == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                                <option value="P" {{ $s->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="birth_place" class="form-control" value="{{ $s->birth_place }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ $s->birth_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp / HP Siswa</label>
                            <input type="text" name="phone" class="form-control" value="{{ $s->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Orang Tua / Wali</label>
                            <input type="text" name="parent_name" class="form-control" value="{{ $s->parent_name }}" placeholder="Nama ayah/ibu/wali">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">📱 WhatsApp Orang Tua <span class="badge bg-success ms-1" style="font-size:9px;">Notifikasi WA</span></label>
                            <input type="text" name="parent_phone" class="form-control" value="{{ $s->parent_phone }}" placeholder="0812... (untuk notif absen ke ortu)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Siswa</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $s->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="graduated" {{ $s->status == 'graduated' ? 'selected' : '' }}>Lulus (Graduated)</option>
                                <option value="transferred" {{ $s->status == 'transferred' ? 'selected' : '' }}>Mutasi (Transferred)</option>
                                <option value="dropped_out" {{ $s->status == 'dropped_out' ? 'selected' : '' }}>Keluar (Dropped Out)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Alamat Tempat Tinggal</label>
                            <textarea name="address" class="form-control" rows="2">{{ $s->address }}</textarea>
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

<!-- Modal Add Student -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('master.students.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Data Siswa Baru (One Data Entry)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-id-card me-1"></i> Identitas Siswa</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap Siswa</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Muhammad Farhan" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">NISN (Wajib)</label>
                            <input type="text" name="nisn" class="form-control" placeholder="006..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">NIS Sekolah</label>
                            <input type="text" name="nis" class="form-control" placeholder="2026...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Rombel / Kelas Penempatan</label>
                            <select name="current_class_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->major?->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Keahlian</label>
                            <select name="major_id" class="form-select" required>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($majors as $m)
                                    <option value="{{ $m->id }}">{{ $m->code }} - {{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jenis Kelamin</label>
                            <select name="gender" class="form-select">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tempat Lahir</label>
                            <input type="text" name="birth_place" class="form-control" placeholder="Bogor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Alamat Tempat Tinggal</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Jl. Raya..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">No. WA Siswa</label>
                            <input type="text" name="phone" class="form-control" placeholder="0812...">
                        </div>
                    </div>

                    <h6 class="fw-bold text-success mb-3"><i class="fa-brands fa-whatsapp me-1"></i> Kontak Orang Tua / Wali <span class="badge bg-success ms-1" style="font-size:9px;">Notifikasi WA Absen</span></h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Orang Tua / Wali</label>
                            <input type="text" name="parent_name" class="form-control" placeholder="Nama Ayah/Ibu/Wali">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">📱 No. WhatsApp Orang Tua</label>
                            <input type="text" name="parent_phone" class="form-control" placeholder="0812... (untuk notif absen otomatis)">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-users me-1"></i> Data Orang Tua / Wali (Opsional)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Ayah</label>
                            <input type="text" name="father_name" class="form-control" placeholder="Nama Ayah Siswa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp Ayah</label>
                            <input type="text" name="father_phone" class="form-control" placeholder="0812...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Ibu</label>
                            <input type="text" name="mother_name" class="form-control" placeholder="Nama Ibu Siswa">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp Ibu</label>
                            <input type="text" name="mother_phone" class="form-control" placeholder="0813...">
                        </div>
                </div>

                <div class="alert alert-info small mt-3 mb-0">
                    <i class="fa-solid fa-circle-info me-1"></i> Akun portal Siswa (Username = NISN) dan Akun Orang Tua (Username = <code>ortu_NISN</code>) akan di-generate secara otomatis.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Siswa</button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Modal Import Siswa -->
<div class="modal fade" id="importStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-import me-2 text-success"></i>Import Data Siswa (Excel/CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('master.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Upload file Excel <strong>.xls/.xlsx</strong> atau <strong>.csv</strong> menggunakan format template.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File Excel / CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required>
                    </div>
                    <div class="text-muted small">
                        <strong>Urutan Kolom:</strong><br>
                        <code>nama, nisn, nis, jenis_kelamin, kelas, jurusan_kode, tempat_lahir, tanggal_lahir, agama, telepon, email</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('master.students.template') }}" class="btn btn-outline-success btn-sm me-auto">
                        <i class="fa-solid fa-download me-1"></i> Unduh Template Excel
                    </a>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Proses Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
