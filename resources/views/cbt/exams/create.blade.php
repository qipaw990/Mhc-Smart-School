@extends('layouts.app')

@section('title', 'Buat Jadwal Ujian CBT')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('cbt.exams.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Ujian
        </a>
        <h4 class="fw-bold mb-1">Jadwalkan Sesi Ujian CBT Baru</h4>
        <p class="text-muted mb-0 small">Atur jadwal pelaksanaan, token ujian, durasi pengerjaan, dan batas toleransi kecurangan browser.</p>
    </div>
</div>

<div class="card card-custom p-4 p-md-5">
    <form action="{{ route('cbt.exams.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Nama / Judul Ujian</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Sumatif Tengah Semester (CBT): Pemrograman Web" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Token Masuk Ujian</label>
                <div class="input-group">
                    <input type="text" name="token" id="tokenInput" class="form-control text-uppercase font-monospace fw-bold" value="{{ strtoupper(Str::random(6)) }}" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('tokenInput').value = Math.random().toString(36).substring(2, 8).toUpperCase()">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilih Bank Soal</label>
                <select name="question_bank_id" id="questionBankSelect" class="form-select" onchange="onBankChange(this)" required>
                    <option value="">-- Pilih Bank Soal --</option>
                    @foreach($banks as $b)
                        <option value="{{ $b->id }}" data-subject-id="{{ $b->subject_id }}">{{ $b->title }} ({{ $b->subject?->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Guru Pengampu</label>
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

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Mata Pelajaran</label>
                @if(isset($isGuru) && $isGuru && $subjects->count() === 1)
                    <input type="hidden" name="subject_id" value="{{ $subjects->first()->id }}">
                    <div class="form-control bg-light fw-semibold">
                        <i class="fa-solid fa-book me-2 text-primary"></i>{{ $subjects->first()->name }}
                    </div>
                @else
                    <select name="subject_id" id="subjectSelect" class="form-select" required>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Waktu Mulai Ujian</label>
                <input type="datetime-local" name="start_time" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold">Waktu Selesai (Batas Akses)</label>
                <input type="datetime-local" name="end_time" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}" required>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Durasi (Menit)</label>
                <input type="number" name="duration_minutes" class="form-control" value="60" min="10" max="240" required>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">KKTP Kelulusan</label>
                <input type="number" name="kktp_score" class="form-control" value="75" min="0" max="100" required>
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-semibold">Pilih Target Rombel Kelas:</label>
                <div class="row g-2">
                    @foreach($classes as $c)
                        <div class="col-md-3 col-sm-6">
                            <div class="form-check p-2 border rounded-3 bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="class_ids[]" value="{{ $c->id }}" id="cls_{{ $c->id }}" checked>
                                <label class="form-check-label fw-bold small ms-2" for="cls_{{ $c->id }}">
                                    {{ $c->name }} ({{ $c->major?->code }})
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Anti-Cheat Settings -->
            <div class="col-md-12">
                <div class="p-3 border rounded-3 bg-light">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-shield-halved text-danger me-2"></i>Pengaturan Anti-Cheat & Keamanan Ujian:</h6>
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="randomize_questions" id="randQ" checked>
                                <label class="form-check-label small fw-semibold" for="randQ">Acak Urutan Soal (Random Questions)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="randomize_options" id="randOpt" checked>
                                <label class="form-check-label small fw-semibold" for="randOpt">Acak Opsi Pilihan Jawaban (Random Options)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Maksimal Toleransi Pindah Tab Browser</label>
                            <input type="number" name="max_tab_switches" class="form-control form-control-sm" value="3" min="1" max="10" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-semibold">Petunjuk Pengerjaan untuk Peserta Didik</label>
                <textarea name="instructions" class="form-control" rows="3">1. Kerjakan soal dengan teliti dan jujur.
2. Dilarang berpindah tab browser atau meminimalkan layar ujian (Pelanggaran akan dicatat oleh sistem proktor).
3. Jawaban Anda tersimpan secara realtime.</textarea>
            </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-paper-plane me-2"></i> TERBITKAN JADWAL UJIAN CBT
            </button>
        </div>
    </form>
</div>

<script>
    function onBankChange(select) {
        const selectedOption = select.options[select.selectedIndex];
        const subjectId = selectedOption.getAttribute('data-subject-id');
        const subjectSelect = document.getElementById('subjectSelect');
        if (subjectSelect && subjectId) {
            subjectSelect.value = subjectId;
        }
    }
</script>
@endsection
