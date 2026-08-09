@extends('layouts.app')

@section('title', 'Capaian (CP) & Tujuan Pembelajaran (TP)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)</h4>
        <p class="text-muted mb-0 small">Struktur hierarki Kurikulum Merdeka: <strong>Mata Pelajaran &rarr; Elemen CP &rarr; Rincian TP</strong>.</p>
    </div>
    @if($selectedSubject)
        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addCpModal">
            <i class="fa-solid fa-plus me-1"></i> Tambah Elemen CP
        </button>
    @endif
</div>

<!-- Select Subject Bar -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('curriculum.cp-tp.index') }}" method="GET" class="row g-2 align-items-center">
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
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Tampilkan CP & TP</button>
        </div>
    </form>
</div>

@if($selectedSubject)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-book-bookmark text-primary me-2"></i>{{ $selectedSubject->name }} 
            <span class="badge bg-info bg-opacity-10 text-info fs-6">Fase {{ $selectedSubject->phase }}</span>
        </h5>
        <div class="small text-muted">{{ $selectedSubject->learningOutcomes->count() }} Elemen Capaian Pembelajaran</div>
    </div>

    @forelse($selectedSubject->learningOutcomes as $cp)
        <div class="card card-custom p-4 mb-4">
            <!-- CP Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3 flex-wrap gap-2">
                <div>
                    <span class="badge bg-primary fs-6 px-2.5 py-1.5 fw-bold me-2">{{ $cp->code }}</span>
                    <span class="fs-5 fw-bold text-dark">{{ $cp->element }}</span>
                    <p class="text-muted small mt-2 mb-0" style="max-width: 800px;">{{ $cp->description }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success fw-semibold" data-bs-toggle="modal" data-bs-target="#addTpModal{{ $cp->id }}">
                        <i class="fa-solid fa-plus me-1"></i> Tambah TP
                    </button>
                    <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#editCpModal{{ $cp->id }}">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <form action="{{ route('curriculum.cp.destroy', $cp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Capaian Pembelajaran ini beserta semua TP di bawahnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>

            <!-- TP List Table -->
            <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-list-check me-1 text-success"></i> Rincian Tujuan Pembelajaran (TP):</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">KODE TP</th>
                            <th style="width: 60px;">URUT</th>
                            <th>DESKRIPSI TUJUAN PEMBELAJARAN</th>
                            <th style="width: 120px;">SEMESTER</th>
                            <th style="width: 100px;">ESTIMASI JP</th>
                            <th style="width: 120px;">MATERI</th>
                            <th style="width: 100px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cp->learningObjectives as $tp)
                            <tr>
                                <td class="fw-bold text-primary font-monospace">{{ $tp->code }}</td>
                                <td class="text-center font-monospace">{{ $tp->order_number }}</td>
                                <td>{{ $tp->description }}</td>
                                <td><span class="badge bg-light text-dark border">Semester {{ $tp->semester_number }}</span></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $tp->estimated_hours }} JP</span></td>
                                <td>
                                    <a href="{{ route('curriculum.materials.index', ['subject_id' => $selectedSubject->id]) }}" class="badge bg-secondary bg-opacity-10 text-secondary text-decoration-none">
                                        <i class="fa-solid fa-folder-open me-1"></i> {{ $tp->materials->count() }} Materi
                                    </a>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-outline-warning text-dark me-1" data-bs-toggle="modal" data-bs-target="#editTpModal{{ $tp->id }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <form action="{{ route('curriculum.tp.destroy', $tp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus TP ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit TP Modal -->
                            <div class="modal fade" id="editTpModal{{ $tp->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('curriculum.tp.update', $tp->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Tujuan Pembelajaran: {{ $tp->code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Kode TP</label>
                                                        <input type="text" name="code" class="form-control" value="{{ $tp->code }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Nomor Urut</label>
                                                        <input type="number" name="order_number" class="form-control" value="{{ $tp->order_number }}" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Deskripsi Tujuan Pembelajaran</label>
                                                    <textarea name="description" class="form-control" rows="3" required>{{ $tp->description }}</textarea>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Semester</label>
                                                        <select name="semester_number" class="form-select">
                                                            <option value="1" {{ $tp->semester_number == 1 ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                                            <option value="2" {{ $tp->semester_number == 2 ? 'selected' : '' }}>Semester 2 (Genap)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold">Estimasi Jam Pelajaran (JP)</label>
                                                        <input type="number" name="estimated_hours" class="form-control" value="{{ $tp->estimated_hours }}" required>
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
                                <td colspan="7" class="text-center text-muted py-3">Belum ada Tujuan Pembelajaran (TP) untuk elemen ini. Silakan klik "+ Tambah TP".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add TP Modal for this CP -->
        <div class="modal fade" id="addTpModal{{ $cp->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('curriculum.tp.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="learning_outcome_id" value="{{ $cp->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Tambah Tujuan Pembelajaran (TP) Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <strong>Elemen CP:</strong> {{ $cp->element }} ({{ $cp->code }})
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Kode TP</label>
                                    <input type="text" name="code" class="form-control" placeholder="Contoh: TP-01.1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Nomor Urut</label>
                                    <input type="number" name="order_number" class="form-control" value="{{ $cp->learningObjectives->count() + 1 }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi Tujuan Pembelajaran</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Peserta didik mampu..." required></textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Semester</label>
                                    <select name="semester_number" class="form-select">
                                        <option value="1">Semester 1 (Ganjil)</option>
                                        <option value="2">Semester 2 (Genap)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Estimasi JP</label>
                                    <input type="number" name="estimated_hours" class="form-control" value="8" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan TP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit CP Modal -->
        <div class="modal fade" id="editCpModal{{ $cp->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('curriculum.cp.update', $cp->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Capaian Pembelajaran: {{ $cp->code }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Kode CP</label>
                                    <input type="text" name="code" class="form-control" value="{{ $cp->code }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Fase</label>
                                    <select name="phase" class="form-select">
                                        <option value="E" {{ $cp->phase == 'E' ? 'selected' : '' }}>Fase E (Kelas X)</option>
                                        <option value="F" {{ $cp->phase == 'F' ? 'selected' : '' }}>Fase F (Kelas XI/XII)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Nama Elemen</label>
                                    <input type="text" name="element" class="form-control" value="{{ $cp->element }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Deskripsi Capaian Pembelajaran (CP)</label>
                                    <textarea name="description" class="form-control" rows="4" required>{{ $cp->description }}</textarea>
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
        <div class="card card-custom p-5 text-center">
            <i class="fa-solid fa-bullseye text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-bold">Belum Ada Capaian Pembelajaran (CP)</h5>
            <p class="text-muted small">Tambahkan Elemen Capaian Pembelajaran untuk mata pelajaran {{ $selectedSubject->name }}.</p>
            <div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCpModal">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Elemen CP Sekarang
                </button>
            </div>
        </div>
    @endforelse

    <!-- Add CP Modal -->
    <div class="modal fade" id="addCpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('curriculum.cp.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Elemen Capaian Pembelajaran (CP)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border small mb-3">
                            <strong>Mata Pelajaran:</strong> {{ $selectedSubject->name }} (Fase {{ $selectedSubject->phase }})
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kode CP</label>
                                <input type="text" name="code" class="form-control" placeholder="Contoh: CP-RPL-02" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Fase Kurikulum</label>
                                <select name="phase" class="form-select">
                                    <option value="E" {{ $selectedSubject->phase == 'E' ? 'selected' : '' }}>Fase E (Kelas X)</option>
                                    <option value="F" {{ $selectedSubject->phase == 'F' ? 'selected' : '' }}>Fase F (Kelas XI/XII)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nama Elemen</label>
                                <input type="text" name="element" class="form-control" placeholder="Contoh: Basis Data Relasional" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Deskripsi Capaian Pembelajaran (CP Resmi)</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Pada akhir fase ini, peserta didik mampu..." required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Elemen CP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
