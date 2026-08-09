@extends('layouts.app')

@section('title', 'Jurnal Mengajar Guru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Jurnal Mengajar Digital Guru</h4>
        <p class="text-muted mb-0 small">Catatan pelaksanaan KBM real-time terintegrasi otomatis dengan <strong>Jadwal Pelajaran, TP Kurikulum Merdeka & Presensi Kelas</strong>.</p>
    </div>
    <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Isi Jurnal Mengajar Hari Ini
    </a>
</div>

<!-- Filter Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('journals.index') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
            <select name="class_id" class="form-select bg-light">
                <option value="">-- Semua Kelas --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->major?->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <select name="teacher_id" class="form-select bg-light">
                <option value="">-- Semua Guru Pengampu --</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Journals Table -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>TANGGAL & JAM</th>
                    <th>GURU PENGAMPU</th>
                    <th>KELAS & MAPEL</th>
                    <th>TUJUAN PEMBELAJARAN (TP) & AKTIVITAS</th>
                    <th>KEHADIRAN SISWA</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journals as $j)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $j->date->format('d M Y') }}</div>
                            <div class="text-muted small">Jam ke-{{ $j->period_start }} s/d {{ $j->period_end }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small"><i class="fa-solid fa-user-tie text-muted me-1"></i> {{ $j->teacher?->full_name }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $j->schoolClass?->name }}</span>
                            <div class="small text-dark mt-1">{{ $j->subject?->name }}</div>
                        </td>
                        <td>
                            @if($j->learningObjective)
                                <div class="badge bg-info bg-opacity-10 text-info fw-bold mb-1">{{ $j->learningObjective->code }}</div>
                            @endif
                            <div class="text-dark small" style="max-width: 320px;">{{ Str::limit($j->topic_activity, 80) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold me-1">H: {{ $j->student_present_count }}</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">Abs: {{ $j->student_absent_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Tersimpan</span>
                        </td>
                        <td>
                            <a href="{{ route('journals.show', $j->id) }}" class="btn btn-xs btn-outline-info text-info me-1" title="Lihat Detail Jurnal">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="{{ route('journals.destroy', $j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurnal mengajar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fa-solid fa-book-open text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold">Belum Ada Catatan Jurnal Mengajar</h6>
                            <p class="small text-muted">Guru dapat mengisi jurnal mengajar harian secara terintegrasi dengan jadwal pelajaran.</p>
                            <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus me-1"></i> Isi Jurnal Mengajar Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $journals->links() }}
    </div>
</div>
@endsection
