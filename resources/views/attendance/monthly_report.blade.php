<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi Bulanan Kelas {{ $selectedClass?->name }} - {{ $monthName }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            padding: 10px;
        }

        /* ── Screen Controls (hidden on print) ─── */
        .screen-controls {
            max-width: 1200px;
            margin: 0 auto 12px;
            background: #fff;
            border-radius: 10px;
            padding: 10px 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .form-group { display: flex; align-items: center; gap: 6px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #475569; }
        .form-select, .form-control {
            padding: 5px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            outline: none;
        }
        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-primary { background: #0284c7; color: #fff; }
        .btn-primary:hover { background: #0369a1; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }

        /* ── Printable Sheet ───────────────────── */
        .print-sheet {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 12px 14px;
            border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #cbd5e1;
        }

        /* Kop Surat — compact */
        .kop-surat {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .kop-logo { width: 48px; height: 48px; object-fit: contain; flex-shrink: 0; }
        .kop-info { flex: 1; text-align: center; }
        .kop-yayasan { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .kop-sekolah { font-size: 13px; font-weight: 900; text-transform: uppercase; margin: 1px 0; }
        .kop-detail { font-size: 8px; color: #334155; line-height: 1.2; }

        /* Report Title — compact */
        .report-title {
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
            text-decoration: underline;
        }

        /* Meta Table — compact */
        .meta-table { width: 100%; margin-bottom: 5px; font-size: 8.5px; }
        .meta-table td { padding: 1px 0; vertical-align: top; }
        .meta-label { font-weight: 700; width: 90px; color: #334155; }
        .meta-colon { width: 8px; text-align: center; font-weight: 700; }
        .meta-val { font-weight: 600; color: #000; }

        /* ── Main Matrix Table — ULTRA COMPACT ── */
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5px;
            margin-bottom: 6px;
            table-layout: fixed;
        }
        .matrix-table th, .matrix-table td {
            border: 0.5px solid #555;
            padding: 1.5px 0.5px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.1;
            overflow: hidden;
        }
        .matrix-table th {
            background: #f1f5f9;
            font-weight: 800;
            font-size: 6px;
            text-transform: uppercase;
        }
        .col-no  { width: 14px; }
        .col-nisn { width: 48px; }
        .col-name { width: 100px; }
        .col-day { width: auto; }  /* auto-fill remaining space */
        .col-recap { width: 14px; }
        .col-pct { width: 22px; }

        .matrix-table td.name-col {
            text-align: left;
            padding-left: 3px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 6px;
        }

        /* Status cell colors (print-safe) */
        .st-H { font-weight: 700; color: #15803d; }
        .st-S { font-weight: 700; color: #b45309; background: #fffbeb; }
        .st-I { font-weight: 700; color: #0369a1; background: #f0f9ff; }
        .st-A { font-weight: 800; color: #b91c1c; background: #fef2f2; }
        .st-T { font-weight: 700; color: #c2410c; background: #fff7ed; }
        .st-empty { color: #d4d4d4; }

        /* ── Footer — compact ─────────────────── */
        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 8px;
            font-size: 8px;
            page-break-inside: avoid;
        }
        .legend-box {
            border: 0.5px solid #000;
            padding: 4px 8px;
            font-size: 7.5px;
        }
        .legend-title { font-weight: 800; margin-bottom: 2px; text-transform: uppercase; font-size: 7px; }
        .legend-item { display: inline-block; margin-right: 8px; }

        .signature-box { text-align: center; min-width: 160px; }
        .sign-title { font-weight: 600; margin-bottom: 36px; font-size: 8px; line-height: 1.3; }
        .sign-name { font-weight: 800; text-decoration: underline; font-size: 8px; }
        .sign-nip { font-size: 7px; color: #475569; }

        /* ── Print Media ──────────────────────── */
        @page { size: A4 landscape; margin: 5mm; }
        @media print {
            body { background: #fff; padding: 0; font-size: 6.5px; }
            .screen-controls { display: none !important; }
            .print-sheet {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .matrix-table { font-size: 6px; }
            .matrix-table th { font-size: 5.5px; }
            .matrix-table td.name-col { font-size: 5.5px; }
            .matrix-table th, .matrix-table td { padding: 1px 0.5px; }
            .kop-logo { width: 40px; height: 40px; }
            .kop-sekolah { font-size: 11px; }
            .kop-yayasan { font-size: 8px; }
            .kop-detail { font-size: 7px; }
            .report-title { font-size: 10px; margin-bottom: 3px; }
            .meta-table { font-size: 7.5px; }
            .matrix-table th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .st-S, .st-I, .st-A, .st-T { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-footer { margin-top: 6px; }
            .sign-title { margin-bottom: 28px; }
        }
    </style>
</head>
<body>

<!-- Screen Controls -->
<div class="screen-controls">
    <form method="GET" action="{{ route('attendance.monthly-report') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <div class="form-group">
            <label>Kelas:</label>
            <select name="class_id" onchange="this.form.submit()">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Bulan:</label>
            <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.submit()">
        </div>
        <button type="submit" class="btn btn-secondary">🔍 Tampilkan</button>
    </form>
    <div style="display:flex; gap:6px;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak / PDF</button>
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>
</div>

<!-- Printable Sheet -->
<div class="print-sheet">

    <!-- Kop Surat -->
    <div class="kop-surat">
        @if($school->logo && file_exists(public_path($school->logo)))
            <img src="{{ asset($school->logo) }}" class="kop-logo" alt="Logo">
        @else
            <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo">
        @endif
        <div class="kop-info">
            <div class="kop-yayasan">YAYASAN MUTHIA HARAPAN MANDIRI</div>
            <div class="kop-sekolah">{{ $school->name ?? 'SMK MUTHIA HARAPAN CICALENGKA' }}</div>
            <div class="kop-detail">
                NPSN: {{ $school->npsn }} | Akreditasi: {{ $school->accreditation }} | {{ $school->address }}, {{ $school->district }}, {{ $school->regency }}
                | Telp: {{ $school->phone }} | Email: {{ $school->email }}
            </div>
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">REKAPITULASI PRESENSI SISWA BULANAN</div>

    <!-- Meta Info -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Kelas</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $selectedClass?->name ?? '-' }}</td>
            <td class="meta-label" style="width:100px;">Tahun Pelajaran</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">2025/2026</td>
        </tr>
        <tr>
            <td class="meta-label">Bulan</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $monthName }}</td>
            <td class="meta-label">Wali Kelas</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $selectedClass?->homeroomTeacher?->name ?? '-' }}</td>
        </tr>
    </table>

    <!-- Matrix Table -->
    <table class="matrix-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-nisn">NISN</th>
                <th rowspan="2" class="col-name">NAMA SISWA</th>
                <th colspan="{{ $daysInMonth }}">TANGGAL ({{ $monthName }})</th>
                <th colspan="5">REKAP</th>
                <th rowspan="2" class="col-pct">%</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="col-day">{{ $d }}</th>
                @endfor
                <th class="col-recap" style="background:#dcfce7;color:#15803d;">H</th>
                <th class="col-recap" style="background:#fef9c3;color:#a16207;">S</th>
                <th class="col-recap" style="background:#e0f2fe;color:#0369a1;">I</th>
                <th class="col-recap" style="background:#fee2e2;color:#b91c1c;">A</th>
                <th class="col-recap" style="background:#ffedd5;color:#c2410c;">T</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matrix as $studentId => $data)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="font-size:5.5px;">{{ $data['student']->nisn ?? '-' }}</td>
                    <td class="name-col">{{ $data['student']->name }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php $st = $data['days'][$d]; @endphp
                        <td class="st-{{ $st == '-' ? 'empty' : $st }}">{{ $st == '-' ? '' : $st }}</td>
                    @endfor
                    <td style="font-weight:700;">{{ $data['count_h'] }}</td>
                    <td style="font-weight:700;">{{ $data['count_s'] }}</td>
                    <td style="font-weight:700;">{{ $data['count_i'] }}</td>
                    <td style="font-weight:700;color:#b91c1c;">{{ $data['count_a'] }}</td>
                    <td style="font-weight:700;color:#c2410c;">{{ $data['count_t'] }}</td>
                    <td style="font-weight:800;">{{ $data['percentage'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 9 + $daysInMonth }}" style="padding:12px;color:#94a3b8;">
                        Tidak ada data siswa pada kelas ini untuk bulan {{ $monthName }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer: Legend + Signatures -->
    <div class="report-footer">
        <div class="legend-box">
            <div class="legend-title">Keterangan:</div>
            <div class="legend-item"><strong class="st-H">H</strong>=Hadir</div>
            <div class="legend-item"><strong class="st-S">S</strong>=Sakit</div>
            <div class="legend-item"><strong class="st-I">I</strong>=Izin</div>
            <div class="legend-item"><strong class="st-A">A</strong>=Alpa</div>
            <div class="legend-item"><strong class="st-T">T</strong>=Terlambat</div>
        </div>

        <div class="signature-box">
            <div class="sign-title">
                Cicalengka, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                Wali Kelas {{ $selectedClass?->name }}
            </div>
            <div class="sign-name">{{ $selectedClass?->homeroomTeacher?->name ?? '......................................' }}</div>
            <div class="sign-nip">NIP/NUPTK: {{ $selectedClass?->homeroomTeacher?->nuptk ?? '-' }}</div>
        </div>

        <div class="signature-box">
            <div class="sign-title">
                Mengetahui,<br>
                Kepala Sekolah
            </div>
            <div class="sign-name">{{ $school->principal_name ?? '......................................' }}</div>
            <div class="sign-nip">NPSN: {{ $school->npsn }}</div>
        </div>
    </div>

</div>

</body>
</html>
