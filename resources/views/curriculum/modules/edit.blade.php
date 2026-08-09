@extends('layouts.app')

@section('title', 'Edit Modul Ajar - ' . $module->title)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('curriculum.modules.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Modul Ajar
        </a>
        <h4 class="fw-bold mb-1">Edit Modul Ajar Kurikulum Merdeka</h4>
        <p class="text-muted mb-0 small">Perbarui data rancangan Modul Ajar <strong>{{ $module->title }}</strong>.</p>
    </div>
    <div>
        <button type="button" class="btn btn-info btn-sm text-white fw-bold shadow-sm" onclick="autoGenerateContent()">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Smart Auto-Generate Template
        </button>
    </div>
</div>

<form action="{{ route('curriculum.modules.update', $module->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- 1. Identitas Modul -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-id-card me-2"></i>I. Informasi Umum & Identitas Modul</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Judul Modul Ajar</label>
                <input type="text" name="title" id="titleInput" class="form-control" value="{{ $module->title }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Guru Pengampu</label>
                @if(isset($isGuru) && $isGuru && $teacher)
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <div class="form-control bg-light text-primary fw-semibold">
                        <i class="fa-solid fa-user-tie me-2"></i>{{ $teacher->full_name }}
                    </div>
                @else
                    <select name="teacher_id" class="form-select" required>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $module->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Mata Pelajaran</label>
                <select name="subject_id" id="subjectSelect" class="form-select" required>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" data-phase="{{ $s->phase }}" {{ $module->subject_id == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Fase</label>
                <select name="phase" id="phaseSelect" class="form-select">
                    <option value="E" {{ $module->phase === 'E' ? 'selected' : '' }}>Fase E</option>
                    <option value="F" {{ $module->phase === 'F' ? 'selected' : '' }}>Fase F</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Kelas / Tingkat</label>
                <select name="grade_level" class="form-select">
                    <option value="X" {{ $module->grade_level === 'X' ? 'selected' : '' }}>Kelas X</option>
                    <option value="XI" {{ $module->grade_level === 'XI' ? 'selected' : '' }}>Kelas XI</option>
                    <option value="XII" {{ $module->grade_level === 'XII' ? 'selected' : '' }}>Kelas XII</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Alokasi Waktu (JP)</label>
                <input type="number" name="allocated_hours" class="form-control" value="{{ $module->allocated_hours }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Elemen Capaian Pembelajaran (CP)</label>
                <select name="learning_outcome_id" id="cpSelect" class="form-select" required>
                    @foreach($learningOutcomes as $cp)
                        <option value="{{ $cp->id }}" data-element="{{ $cp->element }}" {{ $module->learning_outcome_id == $cp->id ? 'selected' : '' }}>{{ $cp->code }} - {{ $cp->element }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Tujuan Pembelajaran (TP)</label>
                <select name="learning_objective_id" id="tpSelect" class="form-select" required>
                    @foreach($learningObjectives as $tp)
                        <option value="{{ $tp->id }}" data-desc="{{ $tp->description }}" {{ $module->learning_objective_id == $tp->id ? 'selected' : '' }}>{{ $tp->code }} - {{ Str::limit($tp->description, 65) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Model Pembelajaran</label>
                <select name="learning_model" id="learningModelSelect" class="form-select">
                    <option value="Problem Based Learning (PBL)" {{ $module->learning_model === 'Problem Based Learning (PBL)' ? 'selected' : '' }}>Problem Based Learning (PBL)</option>
                    <option value="Project Based Learning (PjBL)" {{ $module->learning_model === 'Project Based Learning (PjBL)' ? 'selected' : '' }}>Project Based Learning (PjBL)</option>
                    <option value="Discovery / Inquiry Learning" {{ $module->learning_model === 'Discovery / Inquiry Learning' ? 'selected' : '' }}>Discovery / Inquiry Learning</option>
                    <option value="Teaching Factory (TeFa)" {{ $module->learning_model === 'Teaching Factory (TeFa)' ? 'selected' : '' }}>Teaching Factory (TeFa)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Metode Pembelajaran</label>
                <input type="text" name="methods" id="methodsInput" class="form-control" value="{{ $module->methods }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Target Peserta Didik</label>
                <input type="text" name="target_students" class="form-control" value="{{ $module->target_students }}" required>
            </div>
        </div>
    </div>

    <!-- 2. Rincian Kegiatan Pembelajaran -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-list-ol me-2"></i>II. Skenario Kegiatan Pembelajaran</h5>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label small fw-semibold">1. Kegiatan Pendahuluan (15 Menit)</label>
                <textarea name="preliminary_activities" id="preliminaryInput" class="form-control" rows="3" required>{{ $module->preliminary_activities }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-semibold">2. Kegiatan Inti (150 Menit sesuai Sintaks Model Pembelajaran)</label>
                <textarea name="core_activities" id="coreInput" class="form-control" rows="6" required>{{ $module->core_activities }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-semibold">3. Kegiatan Penutup (15 Menit)</label>
                <textarea name="closing_activities" id="closingInput" class="form-control" rows="3" required>{{ $module->closing_activities }}</textarea>
            </div>
        </div>
    </div>

    <!-- 3. Asesmen & Evaluasi -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clipboard-check me-2"></i>III. Rancangan Asesmen & Remedial/Pengayaan</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Asesmen Awal (Diagnostik)</label>
                <textarea name="diagnostic_assessment" id="diagnosticInput" class="form-control" rows="3">{{ $module->diagnostic_assessment }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Asesmen Proses (Formatif)</label>
                <textarea name="formative_assessment" id="formativeInput" class="form-control" rows="3">{{ $module->formative_assessment }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Asesmen Akhir (Sumatif)</label>
                <textarea name="summative_assessment" id="summativeInput" class="form-control" rows="3">{{ $module->summative_assessment }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Program Remedial</label>
                <textarea name="remedial_plan" id="remedialInput" class="form-control" rows="2">{{ $module->remedial_plan }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Program Pengayaan</label>
                <textarea name="enrichment_plan" id="enrichmentInput" class="form-control" rows="2">{{ $module->enrichment_plan }}</textarea>
            </div>
        </div>
    </div>

    <!-- 4. Lampiran LKPD & Rubrik -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-contract me-2"></i>IV. Lampiran LKPD & Rubrik Penilaian</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Lembar Kerja Peserta Didik (LKPD)</label>
                <textarea name="student_worksheet" id="lkpdInput" class="form-control" rows="5">{{ $module->student_worksheet }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Rubrik Penilaian & Kriteria Ketercapaian (KKTP)</label>
                <textarea name="assessment_rubric" id="rubricInput" class="form-control" rows="5">{{ $module->assessment_rubric }}</textarea>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
            <i class="fa-solid fa-save me-2"></i> SIMPAN PERUBAHAN MODUL AJAR
        </button>
    </div>
</form>

<script>
    function autoGenerateContent() {
        const subjectSelect = document.getElementById('subjectSelect');
        const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
        const cpSelect = document.getElementById('cpSelect');
        const cpElement = cpSelect.options[cpSelect.selectedIndex]?.dataset.element || 'Dasar Kejuruan';
        const tpSelect = document.getElementById('tpSelect');
        const tpDesc = tpSelect.options[tpSelect.selectedIndex]?.dataset.desc || 'Kompetensi kejuruan';
        const model = document.getElementById('learningModelSelect').value;

        document.getElementById('titleInput').value = `Modul Ajar: ${cpElement} (${subjectName.split(' ')[0]})`;

        document.getElementById('preliminaryInput').value = 
`1. Guru membuka pembelajaran dengan salam, berdoa, dan memeriksa kehadiran siswa secara digital melalui sistem MHC Attendance.
2. Apersepsi: Guru mengaitkan materi ${cpElement} dengan implementasi nyata pada industri kerja saat ini.
3. Guru menyampaikan Tujuan Pembelajaran (TP): "${tpDesc}" serta kriteria penilaian yang akan dicapai.`;

        document.getElementById('coreInput').value = 
`Sintaks Model ${model}:
1. Orientasi Masalah: Guru memaparkan studi kasus nyata terkait ${cpElement}.
2. Mengorganisasi Peserta Didik: Siswa dibentuk ke dalam kelompok kerja praktikum (3-4 orang).
3. Membimbing Penyelidikan: Siswa melakukan analisis, perancangan, dan eksperimen sesuai LKPD di bawah bimbingan guru.
4. Mengembangkan & Menyajikan Hasil: Setiap kelompok menguji coba hasil kerja dan mempresentasikan produk/solusi.
5. Menganalisis & Evaluasi: Guru dan siswa melakukan refleksi bersama terhadap solusi yang telah dibuat.`;

        document.getElementById('closingInput').value = 
`1. Siswa bersama guru membuat rangkuman dan kesimpulan esensial materi ${cpElement}.
2. Guru memberikan umpan balik dan refleksi capaian pembelajaran.
3. Guru menginformasikan rencana materi dan asesmen untuk pertemuan selanjutnya.
4. Doa penutup dan salam.`;

        document.getElementById('diagnosticInput').value = 
`Pertanyaan Pemantik Lisan:
1. Apa yang Anda ketahui tentang konsep dasar ${cpElement}?
2. Mengapa kompetensi ini sangat penting di dunia industri SMK?`;

        document.getElementById('formativeInput').value = 
`1. Lembar Observasi Partisipasi & Sikap Kerja Kelompok.
2. Penilaian Unjuk Kerja (Performance Test) saat praktikum.`;

        document.getElementById('summativeInput').value = 
`1. Tes Evaluasi Pengetahuan (Kuis CBT).
2. Penilaian Hasil Portofolio / Produk Kerja Mandiri.`;

        document.getElementById('remedialInput').value = 
`Pendampingan tutor sebaya dan penugasan latihan bertahap bagi peserta didik yang belum mencapai KKTP minimal (Nilai < 75).`;

        document.getElementById('enrichmentInput').value = 
`Pemberian studi kasus kompleks level advance dan eksplorasi implementasi teknologi terkini bagi peserta didik dengan capaian tinggi (Nilai > 85).`;

        document.getElementById('lkpdInput').value = 
`LEMBAR KERJA PESERTA DIDIK (LKPD)
Mata Pelajaran: ${subjectName}
Elemen / Topik: ${cpElement}

A. Tujuan: Siswa mampu ${tpDesc}
B. Alat & Bahan: Komputer / Laptop, Software Tools, Modul Referensi.
C. Langkah Kerja:
1. Lakukan instalasi dan konfigurasi workspace lingkungan kerja.
2. Rancang struktur solusi pemecahan masalah berdasarkan studi kasus.
3. Lakukan pengujian sistem dan catat setiap hasil pengamatan.
D. Pertanyaan Diskusi: Analisis kendala yang ditemukan dan solusinya.`;

        document.getElementById('rubricInput').value = 
`RUBRIK PENILAIAN PRAKTIK:
1. Ketepatan Konsep & Logika Teknis: Bobot 40% (Skor 0-100)
2. Keterampilan Praktik / Unjuk Kerja: Bobot 30% (Skor 0-100)
3. Sikap Kerja, Keselamatan Kerja & Kolaborasi: Bobot 30% (Skor 0-100)
Kriteria Kelulusan (KKTP): Minimal 75`;
    }
</script>
@endsection
