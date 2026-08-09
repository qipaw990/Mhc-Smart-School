<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Projek P5 - {{ $student->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 8.8pt;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 0;
        }

        table {
            page-break-inside: auto;
            border-collapse: collapse;
        }

        tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            page-break-after: auto;
        }

        td, th {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tbody {
            display: table-row-group;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
            text-align: center;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-sub {
            font-size: 8pt;
        }

        .biodata-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8.5pt;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .biodata-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        table.p5-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }

        table.p5-table th, table.p5-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: top;
        }

        table.p5-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }

        .signature-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            font-size: 8.5pt;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
        }

        .no-print {
            background: #0f172a;
            color: #ffffff;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-radius: 6px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <span style="font-size: 9.5pt; font-weight: 600;">Pratinjau Cetak Rapor Projek P5</span>
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 8.5pt;">
            🖨️ Cetak / Simpan PDF Rapor P5
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

    <div style="text-align: center; margin-bottom: 10px;">
        <div style="font-size: 11pt; font-weight: bold;">RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)</div>
        <div style="font-size: 9pt; color: #444;">TAHUN AJARAN {{ $project->academicYear?->name ?? '2026/2027' }} - SEMESTER {{ $project->academicYear?->semesters?->first()?->name ?? 'GANJIL' }}</div>
    </div>

    <!-- Biodata Siswa -->
    <table class="biodata-table">
        <tr>
            <td style="width: 20%;">Nama Siswa</td>
            <td style="width: 2%;">:</td>
            <td style="width: 38%;"><strong>{{ $student->name }}</strong></td>
            <td style="width: 18%;">Kelas</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $student->currentClass?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>NISN / NIS</td>
            <td>:</td>
            <td>{{ $student->nisn }} / {{ $student->nis }}</td>
            <td>Fase</td>
            <td>:</td>
            <td>{{ $project->phase }}</td>
        </tr>
        <tr>
            <td>Tema Projek</td>
            <td>:</td>
            <td colspan="4"><strong>{{ $project->theme_label }}</strong></td>
        </tr>
        <tr>
            <td>Judul Projek</td>
            <td>:</td>
            <td colspan="4">{{ $project->title }}</td>
        </tr>
    </table>

    <!-- Tabel Penilaian Dimensi P5 -->
    <table class="p5-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 35px;">NO</th>
                <th rowspan="2" style="width: 200px;">DIMENSI & ELEMEN P5</th>
                <th rowspan="2">CAPAIAN FASE / TARGET PERILAKU</th>
                <th colspan="4" style="width: 160px;">KATEGORI CAPAIAN</th>
            </tr>
            <tr>
                <th style="width: 40px; font-size: 7.5pt;">MB</th>
                <th style="width: 40px; font-size: 7.5pt;">SB</th>
                <th style="width: 40px; font-size: 7.5pt;">BSH</th>
                <th style="width: 40px; font-size: 7.5pt;">SAB</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($project->dimensions as $dim)
                @php
                    $sc = $scores[$dim->id] ?? null;
                    $scoreVal = $sc?->score;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $no++ }}</td>
                    <td>
                        <div style="font-weight: bold; color: #0284c7;">{{ $dim->dimension_label }}</div>
                        <div style="font-size: 8pt; color: #444;">Elemen: {{ $dim->element_label }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $dim->sub_element }}</div>
                        <div style="font-size: 7.8pt; color: #555; margin-top: 2px;">{{ $dim->target_phase }}</div>
                        @if($sc && $sc->notes)
                            <div style="font-size: 7.5pt; color: #0284c7; margin-top: 3px; font-style: italic;">Catatan: {{ $sc->notes }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $scoreVal === 'MB' ? '✓' : '' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $scoreVal === 'SB' ? '✓' : '' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $scoreVal === 'BSH' ? '✓' : '' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $scoreVal === 'SAB' ? '✓' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 7.5pt; color: #555; margin-bottom: 10px;">
        <strong>Keterangan Skala:</strong> 
        <strong>MB</strong>: Mulai Berkembang | 
        <strong>SB</strong>: Sedang Berkembang | 
        <strong>BSH</strong>: Berkembang Sesuai Harapan | 
        <strong>SAB</strong>: Sangat Berkembang
    </div>

    <!-- Catatan Projek -->
    <div style="border: 1px solid #000; padding: 6px 8px; margin-bottom: 10px; font-size: 8pt; page-break-inside: avoid !important; break-inside: avoid !important;">
        <strong>Catatan Proses & Refleksi Projek:</strong><br>
        {{ $student->name }} telah berpartisipasi aktif dalam rangkaian aktivitas projek bertema {{ $project->theme_label }} dan menunjukkan perkembangan karakter Profil Pelajar Pancasila yang konsisten dan kolaboratif.
    </div>

    <!-- Tanda Tangan Resmi -->
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                Mengetahui,<br>
                Orang Tua / Wali Siswa<br><br><br><br>
                (....................................................)
            </td>
            <td style="width: 33%;">
                Mengetahui,<br>
                Kepala {{ $school->name }}<br><br><br><br>
                <strong><u>{{ $school->principal_name }}</u></strong><br>
                NUPTK. 1975031220000310
            </td>
            <td style="width: 33%;">
                Bogor, {{ date('d F Y') }}<br>
                Koordinator Projek P5<br><br><br><br>
                <strong><u>{{ $project->coordinator?->full_name ?? 'Koordinator P5' }}</u></strong><br>
                NUPTK. {{ $project->coordinator?->nuptk ?? $project->coordinator?->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
