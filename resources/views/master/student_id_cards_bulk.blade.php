<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Kartu Pelajar (Hitam Putih) - {{ $school->name ?? 'SMK' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            padding: 20px;
        }

        /* Controls */
        .screen-controls {
            max-width: 900px;
            margin: 0 auto 24px;
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .controls-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .controls-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .form-group { display: flex; flex-direction: column; gap: 4px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #64748b; }
        .form-group select {
            padding: 8px 12px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            color: #0f172a;
            outline: none;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-dark { background: #0f172a; color: #fff; }
        .btn-dark:hover { background: #1e293b; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .stats-badge {
            background: #f1f5f9;
            color: #0f172a;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #cbd5e1;
        }

        /* ── Card Grid ─────────────────────────────────────── */
        .cards-grid {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(680px, 1fr));
            gap: 20px;
            justify-content: center;
        }

        .card-pair {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        /* ID Card: 85.6mm × 54mm */
        .id-card {
            width: 323px;
            height: 204px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            position: relative;
            flex-shrink: 0;
            border: 1.5px solid #000000;
            background: #ffffff;
        }

        /* Front B&W */
        .card-front {
            background: #ffffff;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            color: #000000;
        }
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
        .school-name { font-size: 7px; font-weight: 900; letter-spacing: 0.3px; text-transform: uppercase; }
        .card-title { font-size: 6px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #d1d5db; }
        .card-body { flex: 1; display: flex; padding: 8px 10px; gap: 10px; align-items: center; background: #fff; }
        .student-photo {
            width: 58px; height: 72px;
            border-radius: 4px;
            border: 1.5px solid #000000;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            color: #64748b; font-size: 24px;
            overflow: hidden; flex-shrink: 0;
        }
        .student-photo img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%); }
        .student-info { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 2px; }
        .student-name { color: #000000; font-size: 10px; font-weight: 900; line-height: 1.2; margin-bottom: 4px; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 2px; }
        .info-row { display: flex; font-size: 6.5px; line-height: 1.3; }
        .info-label { width: 38px; font-weight: 700; color: #334155; text-transform: uppercase; }
        .info-colon { margin-right: 4px; font-weight: 700; }
        .info-value { font-weight: 600; color: #000000; }
        .jurusan-badge { display: inline-block; background: #000000; color: #ffffff; font-size: 5.5px; font-weight: 700; padding: 2px 6px; border-radius: 3px; margin-top: 4px; text-transform: uppercase; width: fit-content; }
        .card-footer { background: #f1f5f9; border-top: 1px solid #000000; padding: 3px 10px; display: flex; justify-content: space-between; align-items: center; }
        .footer-text { color: #000000; font-size: 5.5px; font-weight: 600; }

        /* Back B&W */
        .card-back {
            background: #ffffff;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: #000000;
        }
        .back-header { background: #000000; color: #ffffff; padding: 4px 10px; font-size: 6px; font-weight: 800; letter-spacing: 0.5px; text-align: center; text-transform: uppercase; }
        .back-body { flex: 1; display: flex; padding: 8px 10px; gap: 10px; align-items: center; }
        .qr-area { display: flex; flex-direction: column; align-items: center; gap: 3px; }
        .qr-label { font-size: 5.5px; font-weight: 800; color: #000; text-transform: uppercase; }
        .qr-img { width: 76px; height: 76px; border: 1.5px solid #000000; border-radius: 4px; padding: 2px; background: #ffffff; }
        .qr-nisn { font-size: 6px; font-weight: 800; color: #000; font-family: monospace; }
        .back-info { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .back-info-item { display: flex; flex-direction: column; }
        .back-info-label { font-size: 5px; font-weight: 800; color: #475569; text-transform: uppercase; }
        .back-info-value { font-size: 6.5px; font-weight: 700; color: #000000; }
        .back-divider { width: 100%; height: 1px; background: #000000; margin: 1px 0; }
        .back-footer { background: #000000; color: #ffffff; padding: 4px 10px; display: flex; justify-content: space-between; align-items: center; }
        .back-footer-text { font-size: 5px; color: #e2e8f0; }
        .back-footer-school { font-size: 5.5px; font-weight: 800; color: #ffffff; }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }

        /* ── Print Styles ─────────────────────────────────── */
        @page { margin: 5mm; size: A4; }
        @media print {
            body { background: #fff; padding: 0; }
            .screen-controls { display: none; }
            .cards-grid { max-width: 100%; gap: 4mm; }
            .card-pair { gap: 3mm; }
            .id-card { box-shadow: none; border: 1px solid #000; break-inside: avoid; }
        }
    </style>
</head>
<body>

<!-- Screen Controls -->
<div class="screen-controls">
    <div class="controls-title">🪪 Cetak Massal Kartu Pelajar (Hitam Putih)</div>
    <div class="controls-row">
        <form method="GET" action="{{ route('master.students.id-cards') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group">
                <label>Filter Kelas</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-dark">🔍 Filter</button>
        </form>

        <span class="stats-badge">{{ $students->count() }} Siswa</span>

        <button class="btn btn-dark" onclick="window.print()">🖨️ Cetak Semua (Hitam Putih)</button>
        <a href="{{ route('master.students.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>
</div>

<!-- Cards Grid -->
@if($students->isEmpty())
    <div class="empty-state">
        <div class="icon">🪪</div>
        <p>Tidak ada siswa yang ditemukan.</p>
        <p style="font-size:13px; margin-top:8px;">Pilih kelas atau pastikan ada data siswa aktif.</p>
    </div>
@else
    <div class="cards-grid">
        @foreach($students as $student)
        <div class="card-pair">
            <!-- Front -->
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
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="">
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

            <!-- Back -->
            <div class="id-card">
                <div class="card-back">
                    <div class="back-header">SISTEM PRESENSI QR DIGITAL</div>
                    <div class="back-body">
                        <div class="qr-area">
                            <div class="qr-label">SCAN DISINI</div>
                            <img class="qr-img"
                                 src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($student->nisn) }}"
                                 alt="QR"
                                 loading="lazy">
                            <div class="qr-nisn">NISN: {{ $student->nisn }}</div>
                        </div>
                        <div class="back-info">
                            <div class="back-info-item">
                                <span class="back-info-label">NAMA LENGKAP</span>
                                <span class="back-info-value">{{ $student->name }}</span>
                            </div>
                            <div class="back-divider"></div>
                            <div class="back-info-item">
                                <span class="back-info-label">TTL</span>
                                <span class="back-info-value">{{ $student->birth_place ?? 'Bandung' }}, {{ $student->birth_date?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="back-divider"></div>
                            <div class="back-info-item">
                                <span class="back-info-label">ALAMAT</span>
                                <span class="back-info-value" style="font-size:5.5px;">{{ Str::limit($student->address ?? 'Cicalengka', 40) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="back-footer">
                        <span class="back-footer-text">Kartu wajib dibawa saat sekolah.</span>
                        <span class="back-footer-school">MHC SMART SCHOOL</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

</body>
</html>
