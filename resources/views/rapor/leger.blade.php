@extends('layouts.app')

@section('title', 'Leger Nilai Semester')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('rapor.index', ['class_id' => $selectedClass?->id]) }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke E-Rapor
        </a>
        <h4 class="fw-bold mb-1">Leger Nilai Semester: {{ $selectedClass?->name }}</h4>
        <p class="text-muted mb-0 small">Kompilasi nilai seluruh peserta didik x seluruh mata pelajaran lengkap dengan kalkulasi ranking dan rata-rata kelas.</p>
    </div>
    <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
        <i class="fa-solid fa-print me-1"></i> Cetak Leger Nilai
    </button>
</div>

<!-- Select Class Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('rapor.leger') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-9">
            <select name="class_id" class="form-select bg-light" onchange="this.form.submit()">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->major?->code }}) - Leger Semester Ganjil TA {{ $ay?->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Muat Leger</button>
        </div>
    </form>
</div>

<!-- Leger Matrix Table -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0 text-center small">
            <thead class="table-dark">
                <tr>
                    <th rowspan="2" style="width: 50px;">RANK</th>
                    <th rowspan="2" style="width: 120px;">NISN</th>
                    <th rowspan="2" class="text-start" style="min-width: 180px;">NAMA LENGKAP SISWA</th>
                    <th colspan="{{ $subjects->count() }}">MATA PELAJARAN</th>
                    <th rowspan="2" style="width: 90px;" class="bg-primary">RATA-RATA</th>
                    <th colspan="3">PRESENSI</th>
                </tr>
                <tr>
                    @foreach($subjects as $s)
                        <th style="font-size: 0.72rem; min-width: 65px;" title="{{ $s->name }}">{{ $s->code }}</th>
                    @endforeach
                    <th style="width: 40px;" class="bg-info text-white">S</th>
                    <th style="width: 40px;" class="bg-warning text-dark">I</th>
                    <th style="width: 40px;" class="bg-danger text-white">A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportCards as $rc)
                    @php
                        $gradeMap = $rc->grades->keyBy('subject_id');
                        $avg = $rc->average_score;
                    @endphp
                    <tr>
                        <td class="fw-bold">
                            @if($rc->class_rank === 1)
                                <span class="badge bg-warning text-dark">#1</span>
                            @else
                                #{{ $rc->class_rank }}
                            @endif
                        </td>
                        <td class="font-monospace text-muted">{{ $rc->student?->nisn }}</td>
                        <td class="text-start fw-bold text-dark">{{ $rc->student?->name }}</td>
                        
                        @foreach($subjects as $s)
                            @php
                                $score = $gradeMap->get($s->id)?->final_score ?? '-';
                            @endphp
                            <td class="fw-semibold {{ is_numeric($score) && $score < 75 ? 'text-danger bg-danger bg-opacity-10' : '' }}">
                                {{ $score }}
                            </td>
                        @endforeach

                        <td class="fw-bold fs-6 text-primary bg-light">
                            {{ number_format($avg, 2) }}
                        </td>
                        <td>{{ $rc->sick_count }}</td>
                        <td>{{ $rc->permit_count }}</td>
                        <td>{{ $rc->absent_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 6 + $subjects->count() }}" class="text-center text-muted py-4">Belum ada data rapor yang di-generate.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
