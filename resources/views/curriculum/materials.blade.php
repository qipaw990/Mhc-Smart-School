@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Materi & Sumber Belajar Digital</h4>
        <p class="text-muted mb-0 small">Bahan ajar, modul digital, video referensi, dan link interaktif terhubung langsung ke <strong>Tujuan Pembelajaran (TP)</strong>.</p>
    </div>
    @if($selectedSubject)
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
            <i class="fa-solid fa-plus me-1"></i> Tambah Materi Pembelajaran
        </button>
    @endif
</div>

<!-- Select Subject Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('curriculum.materials.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-9">
            <label class="form-label small fw-semibold text-secondary mb-1">Pilih Mata Pelajaran:</label>
            <select name="subject_id" class="form-select bg-light" onchange="this.form.submit()">
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $selectedSubject?->id == $s->id ? 'selected' : '' }}>
                        [{{ $s->code }}] {{ $s->name }} (Fase {{ $s->phase }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Muat Materi</button>
        </div>
    </form>
</div>

@if($selectedSubject)
    @php $hasMaterials = false; @endphp
    @foreach($selectedSubject->learningOutcomes as $cp)
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                <span class="badge bg-primary fs-6">{{ $cp->code }}</span>
                <h5 class="fw-bold text-dark mb-0">{{ $cp->element }}</h5>
            </div>

            @foreach($cp->learningObjectives as $tp)
                <div class="mb-4 bg-light p-3 rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-primary">
                            <i class="fa-solid fa-bullseye me-1"></i> {{ $tp->code }}: {{ $tp->description }}
                        </div>
                        <span class="badge bg-light text-dark border">{{ $tp->materials->count() }} Sumber Belajar</span>
                    </div>

                    <div class="row g-3 mt-1">
                        @forelse($tp->materials as $mat)
                            @php $hasMaterials = true; @endphp
                            <div class="col-md-6">
                                <div class="card p-3 border h-100 shadow-sm bg-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0">{{ $mat->title }}</h6>
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <button class="btn btn-xs text-warning p-0 me-1" data-bs-toggle="modal" data-bs-target="#editMaterialModal{{ $mat->id }}" title="Edit Materi">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('curriculum.materials.destroy', $mat->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs text-danger p-0" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2">{{ $mat->description ?? 'Tidak ada deskripsi tambahan.' }}</p>
                                    
                                    <div class="d-flex align-items-center gap-2 mt-auto pt-2 border-top small">
                                        <span class="text-muted"><i class="fa-regular fa-clock me-1"></i> {{ $mat->estimated_minutes }} mnt</span>
                                        @if($mat->video_url)
                                            <a href="{{ $mat->video_url }}" target="_blank" class="badge bg-danger text-decoration-none">
                                                <i class="fa-brands fa-youtube me-1"></i> Video
                                            </a>
                                        @endif
                                        @if($mat->external_link)
                                            <a href="{{ $mat->external_link }}" target="_blank" class="badge bg-info text-decoration-none">
                                                <i class="fa-solid fa-link me-1"></i> Referensi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Edit Material -->
                            <div class="modal fade" id="editMaterialModal{{ $mat->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form action="{{ route('curriculum.materials.update', $mat->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Materi Pembelajaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Pilih Tujuan Pembelajaran (TP)</label>
                                                    <select name="learning_objective_id" class="form-select" required>
                                                        @foreach($selectedSubject->learningOutcomes as $subCp)
                                                            <optgroup label="Elemen CP: {{ $subCp->element }}">
                                                                @foreach($subCp->learningObjectives as $subTp)
                                                                    <option value="{{ $subTp->id }}" {{ $mat->learning_objective_id == $subTp->id ? 'selected' : '' }}>
                                                                        {{ $subTp->code }} - {{ Str::limit($subTp->description, 60) }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Judul Materi Pembelajaran</label>
                                                    <input type="text" name="title" class="form-control" value="{{ $mat->title }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Deskripsi / Ringkasan Materi</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $mat->description }}</textarea>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Link Video (YouTube / Drive)</label>
                                                        <input type="url" name="video_url" class="form-control" value="{{ $mat->video_url }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Link Sumber Luar / E-Book</label>
                                                        <input type="url" name="external_link" class="form-control" value="{{ $mat->external_link }}">
                                                    </div>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Estimasi Waktu Belajar (Menit)</label>
                                                        <input type="number" name="estimated_minutes" class="form-control" value="{{ $mat->estimated_minutes }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Urutan Materi</label>
                                                        <input type="number" name="sequence_order" class="form-control" value="{{ $mat->sequence_order }}" required>
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
                            <div class="col-12 text-muted small py-2">
                                Belum ada materi yang ditautkan ke TP ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <!-- Modal Add Material -->
    <div class="modal fade" id="addMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('curriculum.materials.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Materi Pembelajaran Terkait TP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
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
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Judul Materi Pembelajaran</label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Modul Praktik Array 1 Dimensi dan Studi Kasus" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Deskripsi / Ringkasan Materi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Ringkasan poin pembelajaran..."></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Link Video (YouTube / Drive)</label>
                                <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Link Sumber Luar / E-Book</label>
                                <input type="url" name="external_link" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Estimasi Waktu Belajar (Menit)</label>
                                <input type="number" name="estimated_minutes" class="form-control" value="90" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Urutan Materi</label>
                                <input type="number" name="sequence_order" class="form-control" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Materi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
