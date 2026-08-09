@extends('layouts.app')

@section('title', 'Tahun Ajaran & Semester')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Tahun Ajaran & Semester</h4>
        <p class="text-muted mb-0 small">Sistem hanya mengizinkan <strong>satu Tahun Ajaran Aktif</strong> dan <strong>satu Semester Aktif</strong>.</p>
    </div>
    <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addAyModal">
        <i class="fa-solid fa-plus me-1"></i> Tambah Tahun Ajaran
    </button>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>TAHUN AJARAN</th>
                    <th>PERIODE TANGGAL</th>
                    <th>SEMESTER GANJIL</th>
                    <th>SEMESTER GENAP</th>
                    <th>STATUS TAHUN AJARAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicYears as $ay)
                    <tr>
                        <td class="fw-bold fs-6">{{ $ay->name }}</td>
                        <td class="small text-muted">{{ $ay->start_date->format('d M Y') }} s/d {{ $ay->end_date->format('d M Y') }}</td>
                        <td>
                            @php $semGanjil = $ay->semesters->firstWhere('semester_number', 1); @endphp
                            @if($semGanjil)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $semGanjil->is_active ? 'bg-success' : 'bg-light text-dark border' }}">
                                        {{ $semGanjil->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                    @if($ay->is_active && !$semGanjil->is_active)
                                        <form action="{{ route('master.semester.set-active', $semGanjil->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-success py-0 px-1 small" style="font-size: 0.7rem;">Aktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @php $semGenap = $ay->semesters->firstWhere('semester_number', 2); @endphp
                            @if($semGenap)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $semGenap->is_active ? 'bg-success' : 'bg-light text-dark border' }}">
                                        {{ $semGenap->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                    @if($ay->is_active && !$semGenap->is_active)
                                        <form action="{{ route('master.semester.set-active', $semGenap->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-success py-0 px-1 small" style="font-size: 0.7rem;">Aktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($ay->is_active)
                                <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Sedang Aktif</span>
                            @else
                                <span class="badge bg-secondary">Arsip</span>
                            @endif
                        </td>
                        <td>
                            @if(!$ay->is_active)
                                <form action="{{ route('master.academic-year.set-active', $ay->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Aktifkan Tahun Ajaran {{ $ay->name }} sebagai Tahun Ajaran Utama?')">
                                        <i class="fa-solid fa-power-off me-1"></i> Aktifkan
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-success disabled"><i class="fa-solid fa-check me-1"></i> Tahun Aktif</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Academic Year -->
<div class="modal fade" id="addAyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.academic-year.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Tahun Ajaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Tahun Ajaran</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: 2027/2028" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fa-solid fa-info-circle me-1"></i> Semester Ganjil dan Genap akan dibuat secara otomatis di bawah Tahun Ajaran ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Tahun Ajaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
