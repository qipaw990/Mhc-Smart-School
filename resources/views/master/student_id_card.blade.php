<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Pelajar Hitam Putih - {{ $student->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 24px;
        }

        /* ── Print Controls ─────────────────────────────────────── */
        .print-controls {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-dark { background: #0f172a; color: #fff; }
        .btn-dark:hover { background: #1e293b; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }

        /* ── Card Container ──────────────────────────────────────── */
        .card-wrapper {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Standard ID card size: 85.6mm × 54mm @ 96dpi ≈ 323px × 204px */
        .id-card {
            width: 323px;
            height: 204px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            position: relative;
            border: 1.5px solid #000000;
            background: #ffffff;
        }

        /* ── FRONT CARD (Hitam Putih Monochrome + Logo) ─────────── */
        .card-front {
            background: #ffffff;
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: #000;
        }

        /* Header strip - Solid Black */
        .card-header {
            background: #000000;
            color: #ffffff;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .school-logo-box {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            border: 1px solid #ffffff;
            padding: 2px;
        }
        .school-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: grayscale(100%);
        }
        .school-info { flex: 1; }
        .school-name {
            font-size: 7px;
            font-weight: 900;
            letter-spacing: 0.3px;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .card-title {
            font-size: 6px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #d1d5db;
        }

        /* Body */
        .card-body {
            flex: 1;
            display: flex;
            padding: 8px 10px;
            gap: 10px;
            align-items: center;
            background: #ffffff;
        }

        /* Photo - Black & White border */
        .photo-container { flex-shrink: 0; }
        .student-photo {
            width: 58px;
            height: 72px;
            border-radius: 4px;
            border: 1.5px solid #000000;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 24px;
            overflow: hidden;
        }
        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(100%);
        }

        /* Student info */
        .student-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
        }
        .student-name {
            color: #000000;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2px;
        }
        .info-row {
            display: flex;
            font-size: 6.5px;
            line-height: 1.3;
        }
        .info-label {
            width: 38px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }
        .info-colon { margin-right: 4px; font-weight: 700; }
        .info-value {
            font-weight: 600;
            color: #000000;
        }
        .jurusan-badge {
            display: inline-block;
            background: #000000;
            color: #ffffff;
            font-size: 5.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 4px;
            text-transform: uppercase;
            width: fit-content;
        }

        /* Footer */
        .card-footer {
            background: #f1f5f9;
            border-top: 1px solid #000000;
            padding: 3px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .footer-text {
            color: #000000;
            font-size: 5.5px;
            font-weight: 600;
        }

        /* ── BACK CARD (Hitam Putih Monochrome + Logo) ───────────── */
        .card-back {
            width: 100%;
            height: 100%;
            background: #ffffff;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: #000;
        }

        .back-header {
            background: #000000;
            color: #ffffff;
            padding: 4px 10px;
            font-size: 6px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-align: center;
            text-transform: uppercase;
        }

        .back-body {
            flex: 1;
            display: flex;
            padding: 8px 10px;
            gap: 10px;
            align-items: center;
        }

        /* QR Code area */
        .qr-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }
        .qr-label {
            font-size: 5.5px;
            font-weight: 800;
            color: #000;
            text-transform: uppercase;
        }
        .qr-img {
            width: 76px;
            height: 76px;
            border: 1.5px solid #000000;
            border-radius: 4px;
            padding: 2px;
            background: #ffffff;
        }
        .qr-nisn {
            font-size: 6px;
            font-weight: 800;
            color: #000;
            font-family: monospace;
        }

        /* Back info */
        .back-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .back-info-item {
            display: flex;
            flex-direction: column;
        }
        .back-info-label {
            font-size: 5px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
        }
        .back-info-value {
            font-size: 6.5px;
            font-weight: 700;
            color: #000000;
        }
        .back-divider {
            width: 100%;
            height: 1px;
            background: #000000;
            margin: 1px 0;
        }

        .back-footer {
            background: #000000;
            color: #ffffff;
            padding: 4px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-footer-text {
            font-size: 5px;
            color: #e2e8f0;
        }
        .back-footer-school {
            font-size: 5.5px;
            font-weight: 800;
            color: #ffffff;
        }

        /* ── Print Styles ──────────────────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; }
            .print-controls { display: none; }
            .id-card {
                box-shadow: none;
                border: 1px solid #000;
                page-break-inside: avoid;
            }
            .card-wrapper { gap: 5mm; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn btn-dark" onclick="window.print()">
        🖨️ Cetak Kartu (Hitam Putih)
    </button>
    <a href="{{ route('master.students.id-cards') }}" class="btn btn-secondary">
        ← Cetak Massal
    </a>
    <a href="{{ route('master.students.index') }}" class="btn btn-secondary">
        ← Kembali
    </a>
</div>

<div class="card-wrapper">

    {{-- ── DEPAN (FRONT - Hitam Putih + Logo Resmi) ──────────────── --}}
    <div class="id-card">
        <div class="card-front">
            <div class="card-header">
                <div class="school-logo-box">
                    @if(!empty($school->logo) && file_exists(public_path('storage/' . $school->logo)))
                        <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo Sekolah">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Muthia Harapan">
                    @endif
                </div>
                <div class="school-info">
                    <div class="school-name">{{ strtoupper($school->name ?? 'SMK MUTHIA HARAPAN CICALENGKA') }}</div>
                    <div class="card-title">KARTU TANDA PELAJAR</div>
                </div>
            </div>

            <div class="card-body">
                <div class="photo-container">
                    <div class="student-photo">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Foto Siswa">
                        @else
                            👤
                        @endif
                    </div>
                </div>

                <div class="student-info">
                    <div class="student-name">{{ $student->name }}</div>

                    <div class="info-row">
                        <span class="info-label">NISN</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $student->nisn ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NIS</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $student->nis ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">KELAS</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $student->currentClass?->name ?? '-' }}</span>
                    </div>

                    <span class="jurusan-badge">{{ $student->major?->name ?? 'REGULER' }}</span>
                </div>
            </div>

            <div class="card-footer">
                <span class="footer-text">TA 2025/2026</span>
                <span class="footer-text">smkmuthiaharapanclk.sch.id</span>
            </div>
        </div>
    </div>

    {{-- ── BELAKANG (BACK - QR Code Scanner Active) ──────────────── --}}
    <div class="id-card">
        <div class="card-back">
            <div class="back-header">SISTEM PRESENSI QR DIGITAL</div>

            <div class="back-body">
                <div class="qr-area">
                    <div class="qr-label">SCAN DISINI</div>

                    <img class="qr-img"
                         src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($student->nisn) }}"
                         alt="QR Code NISN {{ $student->nisn }}"
                         loading="eager">

                    <div class="qr-nisn">NISN: {{ $student->nisn }}</div>
                </div>

                <div class="back-info">
                    <div class="back-info-item">
                        <span class="back-info-label">NAMA LENGKAP</span>
                        <span class="back-info-value">{{ $student->name }}</span>
                    </div>
                    <div class="back-divider"></div>
                    <div class="back-info-item">
                        <span class="back-info-label">TEMPAT, TGL LAHIR</span>
                        <span class="back-info-value">{{ $student->birth_place ?? 'Bandung' }}, {{ $student->birth_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="back-divider"></div>
                    <div class="back-info-item">
                        <span class="back-info-label">ALAMAT</span>
                        <span class="back-info-value" style="font-size: 5.5px;">{{ Str::limit($student->address ?? 'Cicalengka, Bandung', 45) }}</span>
                    </div>
                </div>
            </div>

            <div class="back-footer">
                <span class="back-footer-text">Kartu ini wajib dibawa saat sekolah.</span>
                <span class="back-footer-school">MHC SMART SCHOOL</span>
            </div>
        </div>
    </div>

</div>

</body>
</html>
