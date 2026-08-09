@extends('layouts.app')

@section('title', 'Bank Soal Multi-Tipe')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0.5">Bank Soal Digital Multi-Tipe</h5>
        <p class="text-muted mb-0" style="font-size: 0.76rem;">Koleksi butir soal <strong>Pilihan Ganda, PG Kompleks, Benar/Salah, Menjodohkan, dan Essay HOTS</strong>.</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankModal">
        <i class="fa-solid fa-plus me-1"></i> Buat Bank Soal Baru
    </button>
</div>

<div class="row g-3">
    @forelse($banks as $b)
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom p-3 h-100 position-relative d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Fase {{ $b->phase }}</span>
                    <span class="badge bg-light text-dark border"><i class="fa-solid fa-list-ol me-1 text-info"></i> {{ $b->questions_count }} Soal</span>
                </div>
                <div class="fw-bold text-dark mb-1" style="font-size: 0.86rem;">{{ $b->title }}</div>
                <div class="text-primary fw-semibold mb-2" style="font-size: 0.76rem;">{{ $b->subject?->name }} ({{ $b->subject?->code }})</div>
                <p class="text-muted mb-2.5 flex-grow-1" style="font-size: 0.75rem; line-height: 1.4;">
                    {{ $b->description ?? 'Tidak ada deskripsi tambahan.' }}
                </p>
                <div class="d-flex justify-content-between align-items-center pt-2.5 border-top mt-auto">
                    <span class="text-muted" style="font-size: 0.72rem;"><i class="fa-solid fa-user-tie me-1"></i> {{ $b->teacher?->full_name }}</span>
                    <div class="d-inline-flex gap-1 align-items-center">
                        <a href="{{ route('cbt.banks.show', $b->id) }}" class="btn btn-xs btn-primary">
                            <i class="fa-solid fa-pen-ruler me-0.5"></i> Kelola Soal
                        </a>
                        <form action="{{ route('cbt.banks.destroy', $b->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus bank soal ini beserta seluruh butir soal di dalamnya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-4">
            <i class="fa-solid fa-folder-open text-muted mb-2 fs-3"></i>
            <div class="fw-bold" style="font-size: 0.84rem;">Belum Ada Bank Soal</div>
            <p class="small text-muted mb-2" style="font-size: 0.75rem;">Buat bank soal baru untuk mulai menyusun butir soal ujian CBT.</p>
            <button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addBankModal">
                <i class="fa-solid fa-plus me-1"></i> Buat Bank Soal Sekarang
            </button>
        </div>
    @endforelse
</div>

@if($banks->hasPages())
    <div class="mt-3">
        {{ $banks->links() }}
    </div>
@endif

<!-- Modal Add Bank Soal -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('cbt.banks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Buat Bank Soal Baru</div>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2.5">
                        <div class="col-md-12">
                            <label class="form-label">Judul Bank Soal</label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Bank Soal Pemrograman Web & Database Fase F" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mata Pelajaran</label>
                            @if(isset($isGuru) && $isGuru && $subjects->count() === 1)
                                <input type="hidden" name="subject_id" value="{{ $subjects->first()->id }}">
                                <div class="form-control bg-light fw-semibold">
                                    <i class="fa-solid fa-book me-2 text-primary"></i>{{ $subjects->first()->name }}
                                </div>
                            @else
                                <select name="subject_id" class="form-select" required>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fase</label>
                            <select name="phase" class="form-select">
                                <option value="E">Fase E (Kelas X)</option>
                                <option value="F" selected>Fase F (Kelas XI/XII)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Guru Pengampu</label>
                            @if(isset($isGuru) && $isGuru && $teacher)
                                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                <div class="form-control bg-light text-primary fw-semibold">
                                    <i class="fa-solid fa-user-tie me-2"></i>{{ $teacher->full_name }}
                                </div>
                            @else
                                <select name="teacher_id" class="form-select" required>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Deskripsi / Keterangan</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi materi ujian..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-xs"><i class="fa-solid fa-save me-1"></i> Simpan Bank Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
