@extends('layouts.app')

@section('title', 'Buat Asesmen Baru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('gradebook.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Asesmen
        </a>
        <h4 class="fw-bold mb-1">Buat Asesmen Kurikulum Merdeka</h4>
        <p class="text-muted mb-0 small">Menyiapkan lembar penilaian formatif / sumatif dengan Kriteria Ketercapaian TP (KKTP).</p>
    </div>
</div>

<div class="card card-custom p-4 p-md-5">
    <form action="{{ route('gradebook.store') }}" method="POST">
        @csrf
        <div class="row g-4">

            {{-- Judul Asesmen --}}
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Judul Asesmen</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Formatif 1: Praktik Live Coding Nested Loop di Lab Komputer" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Jenis Asesmen</label>
                <select name="type" class="form-select" required>
                    <option value="formative">Asesmen Formatif (Tugas/Kuis/Praktik)</option>
                    <option value="summative_tp">Asesmen Sumatif Lingkup Materi (TP)</option>
                    <option value="summative_semester">Asesmen Sumatif Akhir Semester (SAS)</option>
                    <option value="diagnostic">Asesmen Awal (Diagnostik)</option>
                </select>
            </div>

            {{-- Guru Pengampu --}}
            @if($isGuru && $teacher)
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Guru Pengampu</label>
                    <div class="form-control bg-light text-primary fw-semibold">
                        <i class="fa-solid fa-user-tie me-2"></i>{{ $teacher->full_name }}
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Guru Pengampu</label>
                    <select name="teacher_id" class="form-select" required>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Rombel Kelas --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Rombel Kelas</label>
                @if($isGuru && $classes->count() === 1)
                    <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                    <div class="form-control bg-light fw-semibold">
                        <i class="fa-solid fa-users me-2 text-success"></i>{{ $classes->first()->name }}
                    </div>
                @else
                    <select name="class_id" class="form-select" required>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->major?->code }})</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Mata Pelajaran --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Mata Pelajaran</label>
                @if($isGuru && $subjects->count() === 1)
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

            {{-- Tujuan Pembelajaran --}}
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Tujuan Pembelajaran (TP) yang Diuji</label>
                <select name="learning_objective_id" class="form-select">
                    <option value="">-- Asesmen Umum / Semua TP --</option>
                    @foreach($learningObjectives as $tp)
                        <option value="{{ $tp->id }}">{{ $tp->code }} - {{ Str::limit($tp->description, 60) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Skor --}}
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Nilai Minimal KKTP (Kelulusan TP)</label>
                <input type="number" name="kktp_score" class="form-control" value="75" min="0" max="100" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Skor Maksimal</label>
                <input type="number" name="max_score" class="form-control" value="100" min="1" max="100" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Pelaksanaan Asesmen</label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-semibold">Deskripsi / Ruang Lingkup Materi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Rincian materi dan rubrik penilaian yang diujikan..."></textarea>
            </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-save me-2"></i> BUAT ASESMEN
            </button>
        </div>
    </form>
</div>
@endsection
