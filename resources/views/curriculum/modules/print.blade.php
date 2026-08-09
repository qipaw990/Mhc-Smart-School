<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Modul Ajar - {{ $module->title }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 20mm 15mm 20mm 20mm;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-sub {
            font-size: 10pt;
        }

        h2, h3, h4 {
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }

        .whitespace-pre-line {
            white-space: pre-line;
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; background: #e0f2fe; padding: 10px; border-radius: 5px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Sekolah -->
    <table class="header-table">
        <tr>
            <td>
                <div class="header-title">{{ $school->name }}</div>
                <div class="header-sub">{{ $school->address }}, {{ $school->district }}, {{ $school->regency }} - {{ $school->province }}</div>
                <div class="header-sub">Telepon: {{ $school->phone }} | Email: {{ $school->email }} | Website: {{ $school->website }}</div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 13pt; font-weight: bold;">MODUL AJAR KURIKULUM MERDEKA</div>
        <div style="font-size: 12pt; font-weight: bold;">MATA PELAJARAN: {{ strtoupper($module->subject?->name) }}</div>
    </div>

    <div class="section-title">I. INFORMASI UMUM</div>
    <table class="info-table">
        <tr>
            <td style="width: 25%;">Nama Penyusun</td>
            <td style="width: 3%;">:</td>
            <td><strong>{{ $module->teacher?->full_name }}</strong></td>
        </tr>
        <tr>
            <td>Satuan Pendidikan</td>
            <td>:</td>
            <td>{{ $school->name }}</td>
        </tr>
        <tr>
            <td>Fase / Kelas</td>
            <td>:</td>
            <td>Fase {{ $module->phase }} / Kelas {{ $module->grade_level }}</td>
        </tr>
        <tr>
            <td>Alokasi Waktu</td>
            <td>:</td>
            <td>{{ $module->allocated_hours }} Jam Pelajaran (JP)</td>
        </tr>
        <tr>
            <td>Model Pembelajaran</td>
            <td>:</td>
            <td>{{ $module->learning_model }}</td>
        </tr>
        <tr>
            <td>Metode Pembelajaran</td>
            <td>:</td>
            <td>{{ $module->methods }}</td>
        </tr>
        <tr>
            <td>Target Peserta Didik</td>
            <td>:</td>
            <td>{{ $module->target_students }}</td>
        </tr>
    </table>

    <div class="section-title">II. KOMPONEN INTI</div>
    <p><strong>A. Capaian Pembelajaran (Elemen: {{ $module->learningOutcome?->element }}):</strong><br>
    {{ $module->learningOutcome?->description }}</p>

    <p><strong>B. Tujuan Pembelajaran (TP):</strong><br>
    {{ $module->learningObjective?->code }}: {{ $module->learningObjective?->description }}</p>

    <p><strong>C. Skenario Kegiatan Pembelajaran:</strong></p>
    
    <div style="margin-left: 15px; margin-bottom: 10px;">
        <strong>1. Kegiatan Pendahuluan (15 Menit):</strong>
        <div class="whitespace-pre-line">{{ $module->preliminary_activities }}</div>
    </div>

    <div style="margin-left: 15px; margin-bottom: 10px;">
        <strong>2. Kegiatan Inti (150 Menit):</strong>
        <div class="whitespace-pre-line">{{ $module->core_activities }}</div>
    </div>

    <div style="margin-left: 15px; margin-bottom: 10px;">
        <strong>3. Kegiatan Penutup (15 Menit):</strong>
        <div class="whitespace-pre-line">{{ $module->closing_activities }}</div>
    </div>

    <div class="section-title">III. ASESMEN & EVALUASI</div>
    <ul>
        <li><strong>Asesmen Awal (Diagnostik):</strong> {{ $module->diagnostic_assessment ?? '-' }}</li>
        <li><strong>Asesmen Proses (Formatif):</strong> {{ $module->formative_assessment ?? '-' }}</li>
        <li><strong>Asesmen Akhir (Sumatif):</strong> {{ $module->summative_assessment ?? '-' }}</li>
        <li><strong>Program Remedial:</strong> {{ $module->remedial_plan ?? '-' }}</li>
        <li><strong>Program Pengayaan:</strong> {{ $module->enrichment_plan ?? '-' }}</li>
    </ul>

    @if($module->student_worksheet)
        <div class="section-title">IV. LAMPIRAN LKPD</div>
        <div class="box whitespace-pre-line">{{ $module->student_worksheet }}</div>
    @endif

    @if($module->assessment_rubric)
        <div class="section-title">V. RUBRIK PENILAIAN</div>
        <div class="box whitespace-pre-line">{{ $module->assessment_rubric }}</div>
    @endif

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                Kepala {{ $school->name }}<br><br><br><br>
                <strong><u>{{ $school->principal_name }}</u></strong><br>
                NIP. 197503122000031001
            </td>
            <td style="width: 50%; text-align: center;">
                Bogor, {{ date('d F Y') }}<br>
                Guru Mata Pelajaran<br><br><br><br>
                <strong><u>{{ $module->teacher?->full_name }}</u></strong><br>
                NIP. {{ $module->teacher?->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
