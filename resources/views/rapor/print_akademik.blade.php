<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Akademik - {{ $reportCard->student?->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 8.8pt;
            line-height: 1.35;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Strict Page Break Prevention */
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
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
            text-align: center;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .header-title {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-sub {
            font-size: 8pt;
            color: #222222;
        }

        .report-title-box {
            text-align: center;
            margin-bottom: 10px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .report-title {
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-subtitle {
            font-size: 9pt;
            font-weight: 700;
            color: #333333;
        }

        .biodata-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8.5pt;
            border-collapse: collapse;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .biodata-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Grade Table */
        table.grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
            border: 1px solid #000000;
        }

        table.grade-table th {
            background-color: #f1f5f9;
            color: #000000;
            text-align: center;
            font-weight: 800;
            font-size: 8pt;
            padding: 5px 6px;
            border: 1px solid #000000;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table.grade-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .subject-name {
            font-weight: 700;
            color: #000000;
            font-size: 8.5pt;
        }

        .final-score-cell {
            text-align: center;
            font-weight: 800;
            font-size: 9.5pt;
            color: #000000;
        }

        .competency-item {
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .competency-item:last-child {
            margin-bottom: 0;
        }

        .competency-label {
            font-weight: 700;
            color: #000000;
        }

        .competency-text {
            color: #222222;
        }

        .sub-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            font-size: 8pt;
        }

        .sub-table th, .sub-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .sub-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
        }

        .notes-box {
            border: 1px solid #000000;
            padding: 6px 8px;
            margin-bottom: 10px;
            font-size: 8.2pt;
            line-height: 1.35;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-table {
            width: 100%;
            margin-top: 12px;
            font-size: 8.5pt;
            border-collapse: collapse;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
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
        <span style="font-size: 9.5pt; font-weight: 600;">Pratinjau Cetak Rapor Akademik (Kurikulum Merdeka)</span>
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 8.5pt;">
            🖨️ Cetak / Unduh Dokumen Rapor (PDF)
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

    <!-- Judul Rapor -->
    <div class="report-title-box">
        <div class="report-title">LAPORAN HASIL BELAJAR (RAPOR AKADEMIK)</div>
        <div class="report-subtitle">KURIKULUM MERDEKA</div>
    </div>

    <!-- Biodata Peserta Didik -->
    <table class="biodata-table">
        <tr>
            <td style="width: 22%;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 36%;"><strong>{{ $reportCard->student?->name }}</strong></td>
            <td style="width: 18%;">Kelas / Fase</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $reportCard->schoolClass?->name }} / Fase E</td>
        </tr>
        <tr>
            <td>Nomor Induk / NISN</td>
            <td>:</td>
            <td>{{ $reportCard->student?->nisn }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>1 (Ganjil)</td>
        </tr>
        <tr>
            <td>Program Keahlian</td>
            <td>:</td>
            <td>{{ $reportCard->schoolClass?->major?->name }}</td>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>2026/2027</td>
        </tr>
    </table>

    <!-- Tabel Nilai & Capaian TP (Strict Page Break Avoid) -->
    <table class="grade-table">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th style="width: 230px;">MATA PELAJARAN</th>
                <th style="width: 85px;">NILAI AKHIR</th>
                <th>CAPAIAN KOMPETENSI</th>
            </tr>
        </thead>
        <tbody>
            @php $printNo = 1; @endphp
            @foreach($reportCard->grades as $g)
                @if($g->subject)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $printNo++ }}</td>
                        <td>
                            <div class="subject-name">{{ $g->subject->name }}</div>
                        </td>
                        <td class="final-score-cell">
                            {{ number_format($g->final_score, 2) }}
                        </td>
                        <td>
                            <div class="competency-item">
                                <span class="competency-label">Capaian Tertinggi:</span>
                                <span class="competency-text">{{ $g->highest_competency_desc }}</span>
                            </div>
                            <div class="competency-item">
                                <span class="competency-label">Perlu Peningkatan:</span>
                                <span class="competency-text">{{ $g->lowest_competency_desc }}</span>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- Ekstrakurikuler & Presensi Row -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid !important; break-inside: avoid !important;">
        <tr>
            <!-- Ekstrakurikuler (Left) -->
            <td style="width: 60%; vertical-align: top; padding-right: 10px;">
                <div style="font-weight: 700; margin-bottom: 3px; font-size: 8.5pt;">Kegiatan Ekstrakurikuler:</div>
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>Kegiatan</th>
                            <th style="width: 25%;">Predikat</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportCard->extracurriculars as $extra)
                            <tr>
                                <td style="font-weight: 600;">{{ $extra->activity_name }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $extra->predicate }}</td>
                                <td>{{ $extra->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: #666;">Tidak ada catatan kegiatan ekstrakurikuler.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>

            <!-- Presensi (Right) -->
            <td style="width: 40%; vertical-align: top;">
                <div style="font-weight: 700; margin-bottom: 3px; font-size: 8.5pt;">Ketidakhadiran:</div>
                <table class="sub-table">
                    <tr>
                        <td>Sakit (S)</td>
                        <td style="text-align: center; width: 35%; font-weight: 600;">{{ $reportCard->sick_count }} hari</td>
                    </tr>
                    <tr>
                        <td>Izin (I)</td>
                        <td style="text-align: center; font-weight: 600;">{{ $reportCard->permit_count }} hari</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan (A)</td>
                        <td style="text-align: center; font-weight: 600;">{{ $reportCard->absent_count }} hari</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Catatan Wali Kelas -->
    <div class="notes-box">
        <strong>Catatan Wali Kelas:</strong><br>
        {{ $reportCard->homeroom_notes }}
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
                Wali Kelas<br><br><br><br>
                <strong><u>{{ $reportCard->schoolClass?->homeroomTeacher?->full_name ?? 'Wali Kelas' }}</u></strong><br>
                NUPTK. {{ $reportCard->schoolClass?->homeroomTeacher?->nuptk ?? $reportCard->schoolClass?->homeroomTeacher?->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
