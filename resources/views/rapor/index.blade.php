@extends('layouts.app')

@section('title', 'E-Rapor Kurikulum Merdeka')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0.5">E-Rapor Kurikulum Merdeka (ONE DATA SCHOOL)</h5>
        <p class="text-muted mb-0" style="font-size: 0.76rem;">Kompilasi otomatis nilai Formatif, Sumatif TP, dan Sumatif SAS lengkap dengan <strong>Auto-Generate Narasi Capaian TP</strong>.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rapor.leger', ['class_id' => $selectedClass?->id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-table-list me-1"></i> Buka Leger Nilai
        </a>
        @if($selectedClass)
            <form action="{{ route('rapor.generate') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate / Perbarui E-Rapor
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Select Class Bar -->
<div class="card card-custom p-2.5 mb-3">
    <form action="{{ route('rapor.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 text-secondary text-nowrap" style="font-size: 0.78rem;">Pilih Rombel Kelas:</label>
                <select name="class_id" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->major?->name }}) - {{ $c->students_count }} Siswa Terdaftar
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="fa-solid fa-sync me-1"></i> Muat Data Rapor</button>
        </div>
    </form>
</div>

<!-- Report Cards Table -->
<div class="card card-custom p-3">
    <div class="d-flex justify-content-between align-items-center mb-2.5">
        <div class="fw-bold text-dark" style="font-size: 0.82rem;">
            <i class="fa-solid fa-file-invoice text-primary me-1.5"></i>Daftar E-Rapor Peserta Didik: {{ $selectedClass?->name }} (TA {{ $ay?->name }})
        </div>
        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
            {{ $reportCards->count() }} Rapor Siap Cetak
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;" class="text-center">RANK</th>
                    <th style="width: 100px;">NISN</th>
                    <th>NAMA PESERTA DIDIK</th>
                    <th class="text-center" style="width: 100px;">RATA-RATA</th>
                    <th class="text-center" style="width: 90px;">PREDIKAT</th>
                    <th class="text-center" style="width: 110px;">KEHADIRAN</th>
                    <th class="text-center" style="width: 95px;">STATUS</th>
                    <th class="text-center" style="width: 140px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportCards as $rc)
                    @php
                        $avg = $rc->average_score;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">
                            @if($rc->class_rank === 1)
                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-0.5"></i> #1</span>
                            @elseif($rc->class_rank <= 3)
                                <span class="badge bg-primary">#{{ $rc->class_rank }}</span>
                            @else
                                <span class="badge bg-light text-dark border">#{{ $rc->class_rank }}</span>
                            @endif
                        </td>
                        <td class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $rc->student?->nisn }}</td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $rc->student?->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $rc->student?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                        </td>
                        <td class="text-center fw-bold text-primary" style="font-size: 0.82rem;">
                            {{ number_format($avg, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $avg >= 88 ? 'bg-success' : ($avg >= 75 ? 'bg-primary' : 'bg-warning text-dark') }}">
                                {{ $avg >= 88 ? 'A (Sangat Baik)' : ($avg >= 75 ? 'B (Baik)' : 'C (Cukup)') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info bg-opacity-10 text-info me-0.5">S: {{ $rc->sick_count }}</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning me-0.5">I: {{ $rc->permit_count }}</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger">A: {{ $rc->absent_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-0.5"></i> Naik Kelas</span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('rapor.show', $rc->id) }}" class="btn btn-xs btn-outline-primary" title="Lihat & Edit Catatan">
                                    <i class="fa-solid fa-eye me-0.5"></i> Detail
                                </a>
                                <a href="{{ route('rapor.print', $rc->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="Cetak PDF Rapor">
                                    <i class="fa-solid fa-print me-0.5"></i> Cetak PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa-solid fa-file-circle-exclamation text-muted mb-2 fs-3"></i>
                            <div class="fw-bold" style="font-size: 0.82rem;">Belum Ada E-Rapor yang Dibuat untuk Kelas Ini</div>
                            <p class="small text-muted mb-0" style="font-size: 0.75rem;">Klik tombol "Generate / Perbarui E-Rapor" untuk mengompilasi nilai seluruh siswa secara otomatis.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
