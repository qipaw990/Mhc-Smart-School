@extends('layouts.app')

@section('title', 'Presensi Siswa Real-time')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Presensi Siswa (Multi-Method Smart Attendance)</h4>
        <p class="text-muted mb-0 small">Mendukung Dynamic QR, Geolocation radius, Kartu RFID, dan Pencatatan Manual Guru.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('attendance.monthly-report', ['class_id' => $selectedClass?->id]) }}" class="btn btn-outline-info btn-sm fw-bold" target="_blank">
            <i class="fa-solid fa-print me-1"></i> Laporan Bulanan Rombel
        </a>
        <a href="{{ route('attendance.wa-logs') }}" class="btn btn-outline-success btn-sm fw-bold">
            <i class="fa-brands fa-whatsapp me-1"></i> Log WA Terkirim
        </a>
        <a href="{{ route('attendance.qr') }}" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-qrcode me-1"></i> Buka Dynamic QR Scanner
        </a>
        @if($selectedClass)
            <button class="btn btn-outline-secondary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                <i class="fa-solid fa-pen-to-square me-1"></i> Input Manual Rombel
            </button>
        @endif
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('attendance.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
            <label class="form-label small fw-semibold text-secondary mb-1">Pilih Rombel Kelas:</label>
            <select name="class_id" class="form-select bg-light" onchange="this.form.submit()">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->major?->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary mb-1">Tanggal Presensi:</label>
            <input type="date" name="date" class="form-control bg-light" value="{{ $date }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Tampilkan Data</button>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-success border-4">
            <div class="text-muted small fw-semibold">HADIR (H)</div>
            <h3 class="fw-bold text-success mb-0">{{ $presentCount }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-info border-4">
            <div class="text-muted small fw-semibold">SAKIT (S)</div>
            <h3 class="fw-bold text-info mb-0">{{ $sickCount }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-warning border-4">
            <div class="text-muted small fw-semibold">IZIN (I)</div>
            <h3 class="fw-bold text-warning mb-0">{{ $permitCount }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-danger border-4">
            <div class="text-muted small fw-semibold">ALPA (A)</div>
            <h3 class="fw-bold text-danger mb-0">{{ $absentCount }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-secondary border-4">
            <div class="text-muted small fw-semibold">TERLAMBAT (T)</div>
            <h3 class="fw-bold text-secondary mb-0">{{ $lateCount }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-custom p-3 text-center border-start border-primary border-4">
            <div class="text-muted small fw-semibold">TOTAL KELAS</div>
            <h3 class="fw-bold text-primary mb-0">{{ $totalStudents }}</h3>
            <div class="text-muted" style="font-size: 0.7rem;">Siswa Terdaftar</div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Kehadiran Siswa {{ $selectedClass?->name }} (Tanggal: {{ date('d F Y', strtotime($date)) }})
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>NISN / NIS</th>
                    <th>NAMA LENGKAP SISWA</th>
                    <th>STATUS KEHADIRAN</th>
                    <th>WAKTU SCAN</th>
                    <th>METODE</th>
                    <th>DEVICE / CATATAN</th>
                </tr>
            </thead>
            <tbody>
                @if($selectedClass)
                    @forelse($selectedClass->students as $st)
                        @php
                            $att = $attendances->firstWhere('student_id', $st->id);
                        @endphp
                        <tr>
                            <td class="font-monospace fw-bold text-muted small">{{ $st->nisn }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $st->name }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $st->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                            </td>
                            <td>
                                @if($att)
                                    <span class="badge bg-{{ $att->status_badge }} px-2.5 py-1.5 fs-6">
                                        {{ $att->status_label }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border">Belum Presensi</span>
                                @endif
                            </td>
                            <td class="font-monospace small">
                                {{ $att ? date('H:i:s', strtotime($att->time)) : '-' }}
                            </td>
                            <td>
                                @if($att)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-{{ $att->method == 'qr_dynamic' ? 'qrcode' : 'fingerprint' }} me-1"></i>
                                        {{ strtoupper(str_replace('_', ' ', $att->method)) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $att?->notes ?? $att?->device_info ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada siswa di kelas ini.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Manual Attendance -->
@if($selectedClass)
<div class="modal fade" id="manualAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('attendance.manual') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Input Manual Presensi: {{ $selectedClass->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom small text-muted">
                        Tanggal: <strong>{{ date('d F Y', strtotime($date)) }}</strong> | Ubah status kehadiran setiap siswa sesuai bukti keterangan.
                    </div>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>NISN & NAMA</th>
                                    <th class="text-center">STATUS KEHADIRAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedClass->students as $st)
                                    @php
                                        $currentStatus = $attendances->firstWhere('student_id', $st->id)?->status ?? 'H';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $st->name }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $st->nisn }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="statuses[{{ $st->id }}]" id="h_{{ $st->id }}" value="H" {{ $currentStatus == 'H' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success px-2.5" for="h_{{ $st->id }}">Hadir</label>

                                                <input type="radio" class="btn-check" name="statuses[{{ $st->id }}]" id="s_{{ $st->id }}" value="S" {{ $currentStatus == 'S' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-info px-2.5" for="s_{{ $st->id }}">Sakit</label>

                                                <input type="radio" class="btn-check" name="statuses[{{ $st->id }}]" id="i_{{ $st->id }}" value="I" {{ $currentStatus == 'I' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning px-2.5" for="i_{{ $st->id }}">Izin</label>

                                                <input type="radio" class="btn-check" name="statuses[{{ $st->id }}]" id="a_{{ $st->id }}" value="A" {{ $currentStatus == 'A' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-danger px-2.5" for="a_{{ $st->id }}">Alpa</label>

                                                <input type="radio" class="btn-check" name="statuses[{{ $st->id }}]" id="t_{{ $st->id }}" value="T" {{ $currentStatus == 'T' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary px-2.5" for="t_{{ $st->id }}">Telat</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Presensi Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
