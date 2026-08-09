@extends('layouts.app')

@section('title', 'Kelola Soal - ' . $bank->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('cbt.banks.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Bank Soal
        </a>
        <h4 class="fw-bold mb-1">{{ $bank->title }}</h4>
        <p class="text-muted mb-0 small">{{ $bank->subject?->name }} | Fase {{ $bank->phase }} | Guru: <strong>{{ $bank->teacher?->full_name }}</strong> | Total: <strong>{{ $bank->questions->count() }} Butir Soal</strong></p>
    </div>
    <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
        <i class="fa-solid fa-plus me-1"></i> Tambah Butir Soal Baru
    </button>
</div>

<!-- Questions List -->
<div class="vstack gap-4 mb-5">
    @forelse($bank->questions as $q)
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-primary fs-6 px-2 py-1 fw-bold">No. {{ $q->order_number }}</span>
                    <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $q->type_label }}</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-bold text-uppercase">{{ $q->cognitive_level }}</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Tingkat: {{ ucfirst($q->difficulty) }}</span>
                    <span class="badge bg-light text-dark border">Bobot: {{ $q->score_weight }} Poin</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editQuestionModal{{ $q->id }}">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                    </button>
                    <form action="{{ route('cbt.questions.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus butir soal nomor {{ $q->order_number }} ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Hapus</button>
                    </form>
                </div>
            </div>

            <!-- Question Content -->
            <div class="fs-6 text-dark mb-3 fw-normal">
                {!! nl2br(e($q->question_text)) !!}
            </div>

            @if($q->code_snippet)
                <div class="mb-3 p-3 bg-dark text-light rounded-3 font-monospace small" style="white-space: pre-wrap;">
{{ $q->code_snippet }}
                </div>
            @endif

            <!-- Question Options -->
            @if($q->type === 'pg' || $q->type === 'pgk' || $q->type === 'true_false')
                <div class="row g-2 mb-3">
                    @foreach($q->options as $opt)
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border {{ $opt->is_correct ? 'bg-success bg-opacity-10 border-success' : 'bg-light' }} small d-flex align-items-center">
                                <span class="badge {{ $opt->is_correct ? 'bg-success text-white' : 'bg-secondary text-white' }} fw-bold me-2 font-monospace">
                                    {{ $opt->option_label }}
                                </span>
                                <span class="{{ $opt->is_correct ? 'fw-bold text-success' : 'text-dark' }}">{{ $opt->option_text }}</span>
                                @if($opt->is_correct)
                                    <i class="fa-solid fa-check text-success ms-auto"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($q->type === 'matching')
                <div class="row g-2 mb-3">
                    @foreach($q->options as $opt)
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border bg-light small d-flex justify-content-between align-items-center">
                                <strong>{{ $opt->option_text }}</strong>
                                <i class="fa-solid fa-arrow-right text-muted mx-2"></i>
                                <span class="badge bg-primary text-white">{{ $opt->matching_pair }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($q->type === 'essay')
                <div class="alert alert-light border small text-muted mb-0">
                    <i class="fa-solid fa-pen-nib text-primary me-1"></i> Soal Essay/Uraian: Dinilai manual oleh guru pengampu berdasarkan rubrik asesmen.
                </div>
            @endif

            @if($q->explanation)
                <div class="mt-2 pt-2 border-top small text-muted">
                    <strong>Kunci / Pembahasan:</strong> {{ $q->explanation }}
                </div>
            @endif
        </div>

        <!-- Modal Edit Question -->
        <div class="modal fade" id="editQuestionModal{{ $q->id }}" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form action="{{ route('cbt.questions.update', $q->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Butir Soal Nomor {{ $q->order_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Tipe Soal</label>
                                    <select name="type" id="editQuestionTypeSelect{{ $q->id }}" class="form-select" onchange="toggleEditOptionInputs(this.value, {{ $q->id }})" required>
                                        <option value="pg" {{ $q->type === 'pg' ? 'selected' : '' }}>Pilihan Ganda (PG)</option>
                                        <option value="pgk" {{ $q->type === 'pgk' ? 'selected' : '' }}>Pilihan Ganda Kompleks (PGK)</option>
                                        <option value="true_false" {{ $q->type === 'true_false' ? 'selected' : '' }}>Benar / Salah (True/False)</option>
                                        <option value="matching" {{ $q->type === 'matching' ? 'selected' : '' }}>Menjodohkan (Matching)</option>
                                        <option value="essay" {{ $q->type === 'essay' ? 'selected' : '' }}>Essay / Uraian</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Level Kognitif</label>
                                    <select name="cognitive_level" class="form-select">
                                        <option value="lots" {{ $q->cognitive_level === 'lots' ? 'selected' : '' }}>LOTS (C1-C2 Mengingat/Memahami)</option>
                                        <option value="mots" {{ $q->cognitive_level === 'mots' ? 'selected' : '' }}>MOTS (C3 Menerapkan)</option>
                                        <option value="hots" {{ $q->cognitive_level === 'hots' ? 'selected' : '' }}>HOTS (C4-C6 Analisis/Evaluasi/Kreasi)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Tingkat Kesukaran</label>
                                    <select name="difficulty" class="form-select">
                                        <option value="easy" {{ $q->difficulty === 'easy' ? 'selected' : '' }}>Mudah</option>
                                        <option value="medium" {{ $q->difficulty === 'medium' ? 'selected' : '' }}>Sedang</option>
                                        <option value="hard" {{ $q->difficulty === 'hard' ? 'selected' : '' }}>Sukar</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                                    <input type="number" step="0.5" name="score_weight" class="form-control" value="{{ $q->score_weight }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Teks Pertanyaan Soal</label>
                                    <textarea name="question_text" class="form-control" rows="3" placeholder="Tuliskan pertanyaan soal..." required>{{ $q->question_text }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Potongan Kode / Code Snippet (Opsional)</label>
                                    <textarea name="code_snippet" class="form-control font-monospace small bg-light" rows="2" placeholder="Tulis potongan script / program...">{{ $q->code_snippet }}</textarea>
                                </div>

                                <!-- PG Options Area -->
                                <div id="editPgOptionsArea{{ $q->id }}" class="col-12 {{ ($q->type === 'pg' || $q->type === 'pgk') ? '' : 'd-none' }}">
                                    <label class="form-label small fw-semibold">Opsi Jawaban & Kunci Benar (Pilih satu radio untuk kunci jawaban PG):</label>
                                    <div class="vstack gap-2">
                                        @foreach(['A', 'B', 'C', 'D', 'E'] as $idx => $opt)
                                            @php
                                                $existingOpt = $q->options->firstWhere('option_label', $opt);
                                                $isCorrect = $existingOpt?->is_correct;
                                            @endphp
                                            <div class="input-group">
                                                <div class="input-group-text bg-light">
                                                    <input class="form-check-input mt-0 me-2" type="radio" name="correct_option" value="{{ $opt }}" {{ $isCorrect ? 'checked' : '' }}>
                                                    <strong>{{ $opt }}</strong>
                                                </div>
                                                <input type="text" name="options[{{ $opt }}]" class="form-control" placeholder="Pilihan jawaban {{ $opt }}..." value="{{ $existingOpt?->option_text }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- True False Area -->
                                @php
                                    $tfTrueOpt = $q->options->firstWhere('option_label', 'A');
                                    $isTfTrue = $tfTrueOpt?->is_correct ?? true;
                                @endphp
                                <div id="editTfOptionsArea{{ $q->id }}" class="col-12 {{ $q->type === 'true_false' ? '' : 'd-none' }}">
                                    <label class="form-label small fw-semibold">Kunci Jawaban Benar / Salah:</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tf_answer" id="editTfTrue{{ $q->id }}" value="true" {{ $isTfTrue ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-success" for="editTfTrue{{ $q->id }}">BENAR (True)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tf_answer" id="editTfFalse{{ $q->id }}" value="false" {{ !$isTfTrue ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-danger" for="editTfFalse{{ $q->id }}">SALAH (False)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Matching Area -->
                                <div id="editMatchingOptionsArea{{ $q->id }}" class="col-12 {{ $q->type === 'matching' ? '' : 'd-none' }}">
                                    <label class="form-label small fw-semibold">Pasangan Menjodohkan (Kiri & Kanan):</label>
                                    <div class="row g-2">
                                        @php
                                            $matchingOptions = $q->options->values();
                                        @endphp
                                        @for($pairIdx = 0; $pairIdx < 4; $pairIdx++)
                                            @php
                                                $pair = $matchingOptions->get($pairIdx);
                                            @endphp
                                            <div class="col-6">
                                                <input type="text" name="matching_pairs[{{ $pairIdx }}][left]" class="form-control form-control-sm" placeholder="Pernyataan Kiri {{ $pairIdx + 1 }}" value="{{ $pair?->option_text }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="matching_pairs[{{ $pairIdx }}][right]" class="form-control form-control-sm" placeholder="Pasangan Kanan {{ $pairIdx + 1 }}" value="{{ $pair?->matching_pair }}">
                                            </div>
                                        @endfor
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Pembahasan / Catatan Kunci</label>
                                    <textarea name="explanation" class="form-control" rows="2" placeholder="Penjelasan jawaban benar...">{{ $q->explanation }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                <i class="fa-solid fa-save me-1"></i> Simpan Perubahan Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-custom p-5 text-center">
            <i class="fa-solid fa-folder-plus text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-bold">Belum Ada Butir Soal</h5>
            <p class="small text-muted">Silakan klik "+ Tambah Butir Soal Baru" untuk mengisi bank soal ini.</p>
        </div>
    @endforelse
</div>

<!-- Modal Add Question -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('cbt.banks.questions.store', $bank->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Butir Soal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tipe Soal</label>
                            <select name="type" id="questionTypeSelect" class="form-select" onchange="toggleOptionInputs(this.value)" required>
                                <option value="pg">Pilihan Ganda (PG)</option>
                                <option value="pgk">Pilihan Ganda Kompleks (PGK)</option>
                                <option value="true_false">Benar / Salah (True/False)</option>
                                <option value="matching">Menjodohkan (Matching)</option>
                                <option value="essay">Essay / Uraian</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Level Kognitif</label>
                            <select name="cognitive_level" class="form-select">
                                <option value="lots">LOTS (C1-C2 Mengingat/Memahami)</option>
                                <option value="mots" selected>MOTS (C3 Menerapkan)</option>
                                <option value="hots">HOTS (C4-C6 Analisis/Evaluasi/Kreasi)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tingkat Kesukaran</label>
                            <select name="difficulty" class="form-select">
                                <option value="easy">Mudah</option>
                                <option value="medium" selected>Sedang</option>
                                <option value="hard">Sukar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Bobot Poin Soal</label>
                            <input type="number" step="0.5" name="score_weight" class="form-control" value="20" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Teks Pertanyaan Soal</label>
                            <textarea name="question_text" class="form-control" rows="3" placeholder="Tuliskan pertanyaan soal..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Potongan Kode / Code Snippet (Opsional)</label>
                            <textarea name="code_snippet" class="form-control font-monospace small bg-light" rows="2" placeholder="Tulis potongan script / program..."></textarea>
                        </div>

                        <!-- PG Options Area -->
                        <div id="pgOptionsArea" class="col-12">
                            <label class="form-label small fw-semibold">Opsi Jawaban & Kunci Benar (Pilih satu radio untuk kunci jawaban):</label>
                            <div class="vstack gap-2">
                                @foreach(['A', 'B', 'C', 'D', 'E'] as $idx => $opt)
                                    <div class="input-group">
                                        <div class="input-group-text bg-light">
                                            <input class="form-check-input mt-0 me-2" type="radio" name="correct_option" value="{{ $opt }}" {{ $idx === 0 ? 'checked' : '' }}>
                                            <strong>{{ $opt }}</strong>
                                        </div>
                                        <input type="text" name="options[{{ $opt }}]" class="form-control" placeholder="Pilihan jawaban {{ $opt }}..." {{ $idx < 4 ? 'required' : '' }}>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- True False Area -->
                        <div id="tfOptionsArea" class="col-12 d-none">
                            <label class="form-label small fw-semibold">Kunci Jawaban Benar / Salah:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tf_answer" id="tfTrue" value="true" checked>
                                    <label class="form-check-label fw-bold text-success" for="tfTrue">BENAR (True)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tf_answer" id="tfFalse" value="false">
                                    <label class="form-check-label fw-bold text-danger" for="tfFalse">SALAH (False)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Matching Area -->
                        <div id="matchingOptionsArea" class="col-12 d-none">
                            <label class="form-label small fw-semibold">Pasangan Menjodohkan (Kiri & Kanan):</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="matching_pairs[0][left]" class="form-control form-control-sm" placeholder="Pernyataan Kiri 1"></div>
                                <div class="col-6"><input type="text" name="matching_pairs[0][right]" class="form-control form-control-sm" placeholder="Pasangan Kanan 1"></div>
                                <div class="col-6"><input type="text" name="matching_pairs[1][left]" class="form-control form-control-sm" placeholder="Pernyataan Kiri 2"></div>
                                <div class="col-6"><input type="text" name="matching_pairs[1][right]" class="form-control form-control-sm" placeholder="Pasangan Kanan 2"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Pembahasan / Catatan Kunci</label>
                            <textarea name="explanation" class="form-control" rows="2" placeholder="Penjelasan jawaban benar..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Simpan Butir Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleOptionInputs(type) {
        document.getElementById('pgOptionsArea').classList.add('d-none');
        document.getElementById('tfOptionsArea').classList.add('d-none');
        document.getElementById('matchingOptionsArea').classList.add('d-none');

        if (type === 'pg' || type === 'pgk') {
            document.getElementById('pgOptionsArea').classList.remove('d-none');
        } else if (type === 'true_false') {
            document.getElementById('tfOptionsArea').classList.remove('d-none');
        } else if (type === 'matching') {
            document.getElementById('matchingOptionsArea').classList.remove('d-none');
        }
    }

    function toggleEditOptionInputs(type, id) {
        const pgArea = document.getElementById('editPgOptionsArea' + id);
        const tfArea = document.getElementById('editTfOptionsArea' + id);
        const matchingArea = document.getElementById('editMatchingOptionsArea' + id);

        if (pgArea) pgArea.classList.add('d-none');
        if (tfArea) tfArea.classList.add('d-none');
        if (matchingArea) matchingArea.classList.add('d-none');

        if (type === 'pg' || type === 'pgk') {
            if (pgArea) pgArea.classList.remove('d-none');
        } else if (type === 'true_false') {
            if (tfArea) tfArea.classList.remove('d-none');
        } else if (type === 'matching') {
            if (matchingArea) matchingArea.classList.remove('d-none');
        }
    }
</script>
@endsection
