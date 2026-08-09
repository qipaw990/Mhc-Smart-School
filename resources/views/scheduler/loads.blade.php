@extends('layouts.app')

@section('title', 'Beban Mengajar Guru (JP)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Pemetaan Beban Mengajar Guru & Rombel</h4>
        <p class="text-muted mb-0 small">Kalkulasi otomatis beban jam pelajaran (JP) per guru: <strong>Target Standar Kemendikbud = 24 JP/minggu</strong>.</p>
    </div>
    <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addLoadModal">
        <i class="fa-solid fa-plus me-1"></i> Tambah Beban Mengajar
    </button>
</div>

<!-- Teacher Workload Summary Cards -->
<div class="card card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-scale-balanced text-primary me-2"></i>Rekap Beban Mengajar per Guru (Tahun Ajaran {{ $ay?->name }}):</h6>
    <div class="row g-3">
        @foreach($teacherSummaries as $ts)
            <div class="col-md-4 col-sm-6">
                <div class="p-3 border rounded-3 bg-light h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold text-dark">{{ $ts['teacher']->full_name }}</div>
                        <span class="badge {{ $ts['total_hours'] >= 24 ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $ts['total_hours'] }} / 24 JP
                        </span>
                    </div>
                    <div class="text-muted small mb-2">{{ $ts['teacher']->position ?? 'Guru Mata Pelajaran' }}</div>
                    
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $ts['total_hours'] >= 24 ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, ($ts['total_hours'] / 24) * 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1" style="font-size: 0.72rem;">
                        <span>Tercapai: {{ $ts['total_hours'] }} JP</span>
                        <span>{{ $ts['total_hours'] >= 24 ? 'Memenuhi Beban' : 'Kurang ' . (24 - $ts['total_hours']) . ' JP' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Detailed Teaching Loads Table -->
<div class="card card-custom p-4">
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Pemetaan Beban Mengajar:</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>GURU PENGAMPU</th>
                    <th>MATA PELAJARAN</th>
                    <th>ROMBEL KELAS</th>
                    <th>BEBAN JP / MINGGU</th>
                    <th>RUANGAN PREFERENSI</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loads as $l)
                    <tr>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-user-tie text-muted me-1"></i> {{ $l->teacher?->full_name }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $l->subject?->name }}</span>
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $l->subject?->code }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary fs-6">{{ $l->schoolClass?->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning fw-bold fs-6">{{ $l->hours_per_week }} JP</span>
                        </td>
                        <td>
                            <span class="small text-muted">{{ $l->preferredRoom?->name ?? 'Sesuai Ruang Kelas' }}</span>
                        </td>
                        <td>
                            <form action="{{ route('scheduler.loads.destroy', $l->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus beban mengajar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data beban mengajar yang dipetakan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loads->links() }}
    </div>
</div>

<!-- Modal Add Teaching Load -->
<div class="modal fade" id="addLoadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('scheduler.loads.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Pemetaan Beban Mengajar Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Guru Pengampu</label>
                            <select name="teacher_id" class="form-select" required>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mata Pelajaran</label>
                            <select name="subject_id" class="form-select" required>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }} ({{ $s->hours_per_week }} JP)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Rombel Kelas</label>
                            <select name="class_id" class="form-select" required>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->major?->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Beban JP per Minggu</label>
                            <input type="number" name="hours_per_week" class="form-control" value="4" min="1" max="18" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ruangan Preferensi (Opsional)</label>
                            <select name="preferred_room_id" class="form-select">
                                <option value="">-- Gunakan Ruang Kelas --</option>
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">{{ $r->code }} ({{ $r->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Beban Mengajar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
