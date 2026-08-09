@extends('layouts.app')

@section('title', 'Detail E-Rapor - ' . $reportCard->student?->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('rapor.index', ['class_id' => $reportCard->class_id]) }}" class="btn btn-xs btn-outline-secondary mb-1">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Rapor
        </a>
        <h5 class="fw-bold mb-0.5">E-Rapor Peserta Didik: {{ $reportCard->student?->name }}</h5>
        <p class="text-muted mb-0" style="font-size: 0.76rem;">NISN: {{ $reportCard->student?->nisn }} | Kelas: <strong>{{ $reportCard->schoolClass?->name }}</strong> | Peringkat Kelas: <span class="badge bg-warning text-dark">#{{ $reportCard->class_rank }}</span></p>
    </div>
    <div>
        <a href="{{ route('rapor.print', $reportCard->id) }}" target="_blank" class="btn btn-primary btn-sm shadow-sm">
            <i class="fa-solid fa-print me-1"></i> Cetak / Export PDF Resmi
        </a>
    </div>
</div>

<!-- Form Edit Homeroom Notes & Extracurriculars -->
<form action="{{ route('rapor.notes.update', $reportCard->id) }}" method="POST">
    @csrf

    <!-- 1. Tabel Nilai Akademik & Capaian TP -->
    <div class="card card-custom p-3 mb-3">
        <div class="fw-bold text-dark mb-2" style="font-size: 0.84rem;">
            <i class="fa-solid fa-graduation-cap text-primary me-1.5"></i>I. Nilai Capaian Hasil Belajar (Kurikulum Merdeka)
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">NO</th>
                        <th style="width: 240px;">MATA PELAJARAN</th>
                        <th style="width: 95px;" class="text-center">NILAI AKHIR</th>
                        <th>CAPAIAN KOMPETENSI (AUTO-GENERATED DARI TP)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNo = 1; @endphp
                    @foreach($reportCard->grades as $g)
                        @if($g->subject)
                            <tr>
                                <td class="text-center fw-bold">{{ $rowNo++ }}</td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $g->subject->name }}</div>
                                    <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">{{ $g->subject->group_label }}</span>
                                </td>
                                <td class="text-center fw-bold text-dark" style="font-size: 0.85rem;">
                                    {{ number_format($g->final_score, 2) }}
                                </td>
                                <td>
                                    <div class="mb-1" style="font-size: 0.78rem;">
                                        <strong class="text-success">Capaian Tertinggi:</strong> {{ $g->highest_competency_desc }}
                                    </div>
                                    <div class="text-secondary" style="font-size: 0.78rem;">
                                        <strong class="text-warning">Perlu Peningkatan:</strong> {{ $g->lowest_competency_desc }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Kegiatan Ekstrakurikuler (Dynamic Input) -->
    <div class="card card-custom p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold text-dark" style="font-size: 0.84rem;">
                <i class="fa-solid fa-medal text-warning me-1.5"></i>II. Kegiatan Ekstrakurikuler
            </div>
            <button type="button" class="btn btn-xs btn-outline-primary" onclick="addExtraRow()">
                <i class="fa-solid fa-plus me-1"></i> Tambah Kegiatan Ekstrakurikuler
            </button>
        </div>

        <div id="extraContainer" class="vstack gap-2">
            @php 
                $extras = $reportCard->extracurriculars;
                if ($extras->isEmpty()) {
                    $extras = collect([
                        (object)[
                            'activity_name' => 'Pramuka (Wajib)',
                            'predicate' => 'Sangat Baik',
                            'description' => 'Aktif dan disiplin dalam seluruh rangkaian kegiatan kepramukaan.'
                        ]
                    ]);
                }
            @endphp

            @foreach($extras as $idx => $extra)
                <div class="p-2.5 border rounded-3 bg-light extra-row" id="extraRow_{{ $idx }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Nama Kegiatan Ekstrakurikuler</label>
                            <input type="text" name="extracurriculars[{{ $idx }}][name]" class="form-control form-control-sm" value="{{ $extra->activity_name }}" placeholder="Contoh: Pramuka, PMR, Futsal, Robotik..." list="extraSuggestions" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Predikat</label>
                            <select name="extracurriculars[{{ $idx }}][predicate]" class="form-select form-select-sm">
                                <option value="Sangat Baik" {{ ($extra->predicate ?? '') == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                <option value="Baik" {{ ($extra->predicate ?? '') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Cukup" {{ ($extra->predicate ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                <option value="Kurang" {{ ($extra->predicate ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">Keterangan / Capaian Kegiatan</label>
                            <input type="text" name="extracurriculars[{{ $idx }}][description]" class="form-control form-control-sm" value="{{ $extra->description }}" placeholder="Contoh: Menunjukkan dedikasi dan keterampilan yang sangat baik.">
                        </div>
                        <div class="col-md-1 text-end pt-3">
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeExtraRow(this)" title="Hapus Baris">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <datalist id="extraSuggestions">
            <option value="Pramuka (Wajib)">
            <option value="PMR (Palang Merah Remaja)">
            <option value="Paskibra">
            <option value="Futsal & Sepakbola">
            <option value="Basket">
            <option value="Bulu Tangkis">
            <option value="Rohis & Tahfidz Al-Qur'an">
            <option value="English Club & Debate">
            <option value="IT & Robotics Club">
            <option value="Desain Grafis & Multimedia">
            <option value="Seni Musik & Tari Tradisional">
            <option value="Karya Ilmiah Remaja (KIR)">
        </datalist>
    </div>

    <!-- 3. Presensi & Catatan Wali Kelas -->
    <div class="card card-custom p-3 mb-3">
        <div class="fw-bold text-dark mb-2" style="font-size: 0.84rem;">
            <i class="fa-solid fa-clipboard-user text-info me-1.5"></i>III. Ketidakhadiran & Catatan Wali Kelas
        </div>
        <div class="row g-2.5">
            <div class="col-md-2">
                <label class="form-label">Sakit (Hari)</label>
                <input type="number" name="sick_count" class="form-control form-control-sm" value="{{ $reportCard->sick_count }}" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Izin (Hari)</label>
                <input type="number" name="permit_count" class="form-control form-control-sm" value="{{ $reportCard->permit_count }}" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Alpa (Hari)</label>
                <input type="number" name="absent_count" class="form-control form-control-sm" value="{{ $reportCard->absent_count }}" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Keputusan Kenaikan / Kelulusan</label>
                <select name="promotion_status" class="form-select form-select-sm">
                    <option value="naik_kelas" {{ $reportCard->promotion_status == 'naik_kelas' ? 'selected' : '' }}>Naik ke Kelas Berikutnya</option>
                    <option value="tinggal_kelas" {{ $reportCard->promotion_status == 'tinggal_kelas' ? 'selected' : '' }}>Tinggal di Kelas yang Sama</option>
                    <option value="lulus" {{ $reportCard->promotion_status == 'lulus' ? 'selected' : '' }}>Lulus</option>
                    <option value="belum_lulus" {{ $reportCard->promotion_status == 'belum_lulus' ? 'selected' : '' }}>Belum Lulus</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Catatan Wali Kelas</label>
                <textarea name="homeroom_notes" class="form-control form-control-sm" rows="2.5" required>{{ $reportCard->homeroom_notes }}</textarea>
            </div>
        </div>
    </div>

    <div class="text-end mb-4">
        <button type="submit" class="btn btn-primary btn-sm px-4 shadow">
            <i class="fa-solid fa-save me-1"></i> SIMPAN PERUBAHAN RAPOR
        </button>
    </div>
</form>

<script>
    let extraIndex = {{ count($extras) + 10 }};

    function addExtraRow() {
        extraIndex++;
        const container = document.getElementById('extraContainer');
        const div = document.createElement('div');
        div.className = 'p-2.5 border rounded-3 bg-light extra-row';
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label mb-1">Nama Kegiatan Ekstrakurikuler</label>
                    <input type="text" name="extracurriculars[${extraIndex}][name]" class="form-control form-control-sm" placeholder="Contoh: PMR, Paskibra, Futsal..." list="extraSuggestions" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Predikat</label>
                    <select name="extracurriculars[${extraIndex}][predicate]" class="form-select form-select-sm">
                        <option value="Sangat Baik">Sangat Baik</option>
                        <option value="Baik" selected>Baik</option>
                        <option value="Cukup">Cukup</option>
                        <option value="Kurang">Kurang</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label mb-1">Keterangan / Capaian Kegiatan</label>
                    <input type="text" name="extracurriculars[${extraIndex}][description]" class="form-control form-control-sm" placeholder="Contoh: Menunjukkan dedikasi dan keterampilan yang sangat baik.">
                </div>
                <div class="col-md-1 text-end pt-3">
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeExtraRow(this)" title="Hapus Baris">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeExtraRow(btn) {
        const row = btn.closest('.extra-row');
        if (document.querySelectorAll('.extra-row').length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(i => i.value = '');
        }
    }
</script>
@endsection
