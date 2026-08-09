@extends('layouts.app')

@section('title', 'Modul Ajar Generator')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Modul Ajar Generator Kurikulum Merdeka</h4>
        <p class="text-muted mb-0 small">Generator Modul Ajar otomatis lengkap: Identitas, CP, TP, Rincian Kegiatan (PBL/PjBL), Asesmen, LKPD, Rubrik & Export PDF.</p>
    </div>
    <a href="{{ route('curriculum.modules.create') }}" class="btn btn-primary btn-sm fw-bold">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Buat / Generate Modul Ajar Baru
    </a>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>JUDUL MODUL AJAR</th>
                    <th>MATA PELAJARAN</th>
                    <th>FASE & TINGKAT</th>
                    <th>GURU PENYUSUN</th>
                    <th>MODEL PEMBELAJARAN</th>
                    <th>ALOKASI WAKTU</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modules as $m)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $m->title }}</div>
                            <div class="text-muted small"><strong>TP:</strong> {{ $m->learningObjective?->code }} - {{ Str::limit($m->learningObjective?->description, 50) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $m->subject?->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold">Fase {{ $m->phase }} (Kelas {{ $m->grade_level }})</span>
                        </td>
                        <td>
                            <div class="fw-semibold small"><i class="fa-solid fa-user-tie text-muted me-1"></i> {{ $m->teacher?->full_name }}</div>
                        </td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $m->learning_model }}</span></td>
                        <td><strong>{{ $m->allocated_hours }} JP</strong></td>
                        <td>
                            <a href="{{ route('curriculum.modules.show', $m->id) }}" class="btn btn-xs btn-outline-info text-info me-1" title="Lihat Detail">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('curriculum.modules.edit', $m->id) }}" class="btn btn-xs btn-outline-warning text-dark me-1" title="Edit Modul">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="{{ route('curriculum.modules.print', $m->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary me-1" title="Cetak / Export PDF">
                                <i class="fa-solid fa-print"></i> Cetak
                            </a>
                            <form action="{{ route('curriculum.modules.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Modul Ajar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fa-solid fa-file-circle-plus text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold">Belum Ada Modul Ajar yang Dibuat</h6>
                            <p class="small text-muted">Gunakan Modul Ajar Generator untuk menyusun perangkat pembelajaran Kurikulum Merdeka.</p>
                            <a href="{{ route('curriculum.modules.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Generate Modul Ajar Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $modules->links() }}
    </div>
</div>
@endsection
