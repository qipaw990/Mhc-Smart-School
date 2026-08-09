@extends('layouts.app')

@section('title', 'Buat Projek P5 Baru')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('p5.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Projek
        </a>
        <h4 class="fw-bold mb-1">Rancang Modul Projek P5 Baru</h4>
        <p class="text-muted mb-0 small">Pilih tema, rumuskan judul projek, dan tentukan dimensi profil pelajar Pancasila yang akan dikembangkan.</p>
    </div>
</div>

<div class="card card-custom p-4 p-md-5">
    <form action="{{ route('p5.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Tema Projek P5</label>
                <select name="theme" class="form-select" required>
                    <option value="Kebekerjaan">Kebekerjaan (Khas SMK)</option>
                    <option value="Gaya Hidup Berkelanjutan">Gaya Hidup Berkelanjutan</option>
                    <option value="Rekayasa & Teknologi">Rekayasa & Teknologi</option>
                    <option value="Kewirausahaan">Kewirausahaan</option>
                    <option value="Bangunlah Jiwa dan Raganya">Bangunlah Jiwa dan Raganya</option>
                    <option value="Bhinneka Tunggal Ika">Bhinneka Tunggal Ika</option>
                    <option value="Suara Demokrasi">Suara Demokrasi</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold">Target Rombel Kelas</label>
                <select name="class_id" class="form-select" required>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->major?->code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Judul Projek P5</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Membangun Portofolio Digital Software Developer Profesional" required>
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Deskripsi / Ringkasan Pelaksanaan Projek</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Uraian kegiatan yang dilakukan peserta didik selama pengerjaan projek..." required></textarea>
            </div>

            <!-- Dimensi Profil -->
            <div class="col-12">
                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-layer-group text-primary me-2"></i>Dimensi & Sub-Elemen yang Dinilai:</h6>
                
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Dimensi 1</label>
                            <input type="text" name="dimensions[0][name]" class="form-control form-control-sm" value="Mandiri" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Elemen</label>
                            <input type="text" name="dimensions[0][element]" class="form-control form-control-sm" value="Pemahaman diri dan situasi yang dihadapi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sub-Elemen</label>
                            <input type="text" name="dimensions[0][sub_element]" class="form-control form-control-sm" value="Mengenali kualitas dan minat diri serta tantangan dunia kerja" required>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Dimensi 2</label>
                            <input type="text" name="dimensions[1][name]" class="form-control form-control-sm" value="Bernalar Kritis" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Elemen</label>
                            <input type="text" name="dimensions[1][element]" class="form-control form-control-sm" value="Refleksi pemikiran dan proses berpikir" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sub-Elemen</label>
                            <input type="text" name="dimensions[1][sub_element]" class="form-control form-control-sm" value="Mengidentifikasi dan mengolah informasi solusi komputasi" required>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Dimensi 3</label>
                            <input type="text" name="dimensions[2][name]" class="form-control form-control-sm" value="Kreatif" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Elemen</label>
                            <input type="text" name="dimensions[2][element]" class="form-control form-control-sm" value="Menghasilkan gagasan yang orisinal" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sub-Elemen</label>
                            <input type="text" name="dimensions[2][sub_element]" class="form-control form-control-sm" value="Mengekspresikan ide dalam karya aplikasi berbasis web" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                <i class="fa-solid fa-save me-2"></i> SIMPAN PROJEK P5
            </button>
        </div>
    </form>
</div>
@endsection
