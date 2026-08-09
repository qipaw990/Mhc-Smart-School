@extends('layouts.app')

@section('title', 'Alur Tujuan Pembelajaran (ATP) Builder')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Alur Tujuan Pembelajaran (ATP) Timeline Builder</h4>
        <p class="text-muted mb-0 small">Visualisasi alur kronologis minggu ke minggu, distribusi JP, dan integrasi asesmen Kurikulum Merdeka.</p>
    </div>
    @if($learningPath)
        <div class="d-flex gap-2">
            <button class="btn btn-outline-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#editHeaderModal">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Header ATP
            </button>
            <form action="{{ route('curriculum.atp.header.destroy', $learningPath->id) }}" method="POST" onsubmit="return confirm('Hapus seluruh kerangka dokumen ATP beserta seluruh timeline ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="fa-solid fa-trash me-1"></i> Hapus ATP
                </button>
            </form>
            <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Alokasi Minggu (TP)
            </button>
        </div>
    @endif
</div>

<!-- Select Subject Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('curriculum.atp.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-9">
            <label class="form-label small fw-semibold text-secondary mb-1">Pilih Mata Pelajaran:</label>
            <select name="subject_id" class="form-select bg-light" onchange="this.form.submit()">
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $selectedSubject?->id == $s->id ? 'selected' : '' }}>
                        [{{ $s->code }}] {{ $s->name }} (Fase {{ $s->phase }} - {{ $s->group_label }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Muat Timeline ATP</button>
        </div>
    </form>
</div>

@if($selectedSubject)
    @if(!$learningPath)
        <div class="card card-custom p-5 text-center">
            <i class="fa-solid fa-timeline text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-bold">Kerangka ATP Belum Dibuat</h5>
            <p class="text-muted small">Buat kerangka Alur Tujuan Pembelajaran (ATP) untuk mata pelajaran {{ $selectedSubject->name }}.</p>
            <div class="col-md-6 mx-auto">
                <form action="{{ route('curriculum.atp.header.store') }}" method="POST" class="text-start border p-4 rounded-3 bg-light">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <input type="hidden" name="phase" value="{{ $selectedSubject->phase }}">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Dokumen ATP</label>
                        <input type="text" name="title" class="form-control" value="ATP {{ $selectedSubject->name }} Fase {{ $selectedSubject->phase }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Semester</label>
                        <select name="semester_number" class="form-select">
                            <option value="1">Semester 1 (Ganjil)</option>
                            <option value="2">Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Alur / Rasionalisasi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Rasional alur pembelajaran dari pengenalan konsep dasar menuju implementasi projek..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-plus me-1"></i> Buat Kerangka ATP</button>
                </form>
            </div>
        </div>
    @else
        <!-- ATP Header Summary -->
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $learningPath->title }}</h5>
                    <div class="text-muted small">
                        Fase {{ $learningPath->phase }} | Semester {{ $learningPath->semester_number }} (Ganjil) | 
                        Total Terdistribusi: <strong>{{ $learningPath->items->sum('hour_allocation') }} JP</strong>
                    </div>
                </div>
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2">
                        <i class="fa-solid fa-circle-check me-1"></i> ATP Aktif TA {{ $ay->name }}
                    </span>
                </div>
            </div>
            @if($learningPath->description)
                <p class="small text-muted mt-2 mb-0 border-top pt-2">{{ $learningPath->description }}</p>
            @endif
        </div>

        <!-- Modal Edit Header ATP -->
        <div class="modal fade" id="editHeaderModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('curriculum.atp.header.update', $learningPath->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Header Dokumen ATP</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Judul Dokumen ATP</label>
                                <input type="text" name="title" class="form-control" value="{{ $learningPath->title }}" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Fase</label>
                                    <select name="phase" class="form-select">
                                        <option value="E" {{ $learningPath->phase === 'E' ? 'selected' : '' }}>Fase E</option>
                                        <option value="F" {{ $learningPath->phase === 'F' ? 'selected' : '' }}>Fase F</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Semester</label>
                                    <select name="semester_number" class="form-select">
                                        <option value="1" {{ $learningPath->semester_number == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                        <option value="2" {{ $learningPath->semester_number == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi Alur / Rasionalisasi</label>
                                <textarea name="description" class="form-control" rows="3">{{ $learningPath->description }}</textarea>
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

        <!-- ATP Visual Timeline List -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Alur Pembelajaran Kronologis (Minggu ke Minggu):</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">MINGGU</th>
                            <th style="width: 120px;">KODE TP</th>
                            <th>TOPIK & ELEMEN CP</th>
                            <th style="width: 100px;">ALOKASI JP</th>
                            <th>RENCANA ASESMEN</th>
                            <th style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($learningPath->items as $item)
                            <tr>
                                <td class="fw-bold text-center">
                                    <span class="badge bg-primary px-2.5 py-1.5 fs-6">Mg-{{ $item->week_number }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $item->learningObjective?->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->topic }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <strong>TP:</strong> {{ $item->learningObjective?->description }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-bold fs-6">
                                        {{ $item->hour_allocation }} JP
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-secondary">{{ $item->assessment_plan ?? 'Formatif / Observasi' }}</div>
                                </td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit Item ATP">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('curriculum.atp.item.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus minggu ini dari alur ATP?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit ATP Item -->
                            <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form action="{{ route('curriculum.atp.item.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Langkah Alur ATP (Minggu ke-{{ $item->week_number }})</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Minggu Ke-</label>
                                                        <input type="number" name="week_number" class="form-control" value="{{ $item->week_number }}" min="1" max="24" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-semibold">Alokasi Jam (JP)</label>
                                                        <input type="number" name="hour_allocation" class="form-control" value="{{ $item->hour_allocation }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Pilih Tujuan Pembelajaran (TP)</label>
                                                        <select name="learning_objective_id" class="form-select" required>
                                                            @foreach($selectedSubject->learningOutcomes as $cp)
                                                                <optgroup label="Elemen CP: {{ $cp->element }}">
                                                                    @foreach($cp->learningObjectives as $tp)
                                                                        <option value="{{ $tp->id }}" {{ $item->learning_objective_id == $tp->id ? 'selected' : '' }}>
                                                                            {{ $tp->code }} - {{ Str::limit($tp->description, 60) }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Topik / Ruang Lingkup Materi Minggu Ini</label>
                                                        <input type="text" name="topic" class="form-control" value="{{ $item->topic }}" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold">Rencana Asesmen (Diagnostik / Formatif / Sumatif)</label>
                                                        <textarea name="assessment_plan" class="form-control" rows="2">{{ $item->assessment_plan }}</textarea>
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
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada alokasi minggu di timeline ATP. Silakan klik "+ Tambah Alokasi Minggu (TP)".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Add ATP Item -->
        <div class="modal fade" id="addItemModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('curriculum.atp.item.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="learning_path_id" value="{{ $learningPath->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Tambah Langkah Alur TP ke Timeline ATP</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Minggu Ke-</label>
                                    <input type="number" name="week_number" class="form-control" value="{{ ($learningPath->items->max('week_number') ?? 0) + 1 }}" min="1" max="24" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Alokasi Jam (JP)</label>
                                    <input type="number" name="hour_allocation" class="form-control" value="{{ $selectedSubject->hours_per_week }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Pilih Tujuan Pembelajaran (TP)</label>
                                    <select name="learning_objective_id" class="form-select" required>
                                        @foreach($selectedSubject->learningOutcomes as $cp)
                                            <optgroup label="Elemen CP: {{ $cp->element }}">
                                                @foreach($cp->learningObjectives as $tp)
                                                    <option value="{{ $tp->id }}">{{ $tp->code }} - {{ Str::limit($tp->description, 60) }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Topik / Ruang Lingkup Materi Minggu Ini</label>
                                    <input type="text" name="topic" class="form-control" placeholder="Contoh: Implementasi Struktur Percabangan Bersarang (Nested IF) pada Kasus Diskon" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Rencana Asesmen (Diagnostik / Formatif / Sumatif)</label>
                                    <textarea name="assessment_plan" class="form-control" rows="2" placeholder="Contoh: Formatif unjuk kerja praktikum live coding di lab"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan ke Timeline ATP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif
@endsection
