@extends('layouts.app')

@section('title', 'Smart QR Presensi - Dual Mode')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Rekap Presensi
        </a>
        <h4 class="fw-bold mb-1">Dynamic QR Code Presensi (Anti-Cheat)</h4>
        <p class="text-muted mb-0 small">Dukungan 2 mode: Kode QR berganti otomatis 15-30 detik & Guru Scan QR Kartu Pelajar.</p>
    </div>
</div>

<!-- Mode Selection Nav Tabs -->
<ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 shadow-sm border" id="qrTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold py-2.5" id="mode-student-tab" data-bs-toggle="tab" data-bs-target="#mode-student" type="button" role="tab">
            <i class="fa-solid fa-qrcode me-2"></i> Metode 1: Siswa Scan QR Guru (Dynamic QR Layar)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2.5" id="mode-teacher-tab" data-bs-toggle="tab" data-bs-target="#mode-teacher" type="button" role="tab">
            <i class="fa-solid fa-id-card-clip me-2"></i> Metode 2: Guru Scan QR Kartu Siswa (Kamera Scanner)
        </button>
    </li>
</ul>

<!-- Select Active Class Session -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('attendance.qr') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-md-9">
            <label class="form-label small fw-semibold text-secondary mb-1">Pilih Sesi Jadwal Mengajar Hari Ini:</label>
            <select name="schedule_item_id" id="activeScheduleId" class="form-select bg-light" onchange="this.form.submit()">
                @foreach($scheduleItems as $si)
                    <option value="{{ $si->id }}" {{ $selectedItem?->id == $si->id ? 'selected' : '' }}>
                        [{{ $si->day }} Jam {{ $si->period }}] {{ $si->subject?->name }} - Kelas: {{ $si->schoolClass?->name }} (Guru: {{ $si->teacher?->name }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-sync me-1"></i> Tampilkan QR / Mulai</button>
        </div>
    </form>
</div>

<div class="tab-content" id="qrTabsContent">
    
    <!-- TAB 1: DYNAMIC QR CODE FOR STUDENTS TO SCAN TEACHER'S SCREEN -->
    <div class="tab-pane fade show active" id="mode-student" role="tabpanel">
        @if($selectedItem)
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card card-custom p-4 p-md-5 text-center shadow-lg border-primary border-2">
                        <div class="badge bg-primary bg-opacity-10 text-primary fs-6 mb-2 px-3 py-1.5 fw-bold">
                            {{ $selectedItem->schoolClass?->name }} | {{ $selectedItem->subject?->name }}
                        </div>
                        <h5 class="fw-bold text-dark mb-1">SCAN QR CODE UNTUK PRESENSI</h5>
                        <p class="small text-muted mb-4">Arahkan kamera aplikasi MHC Smart Student ke layar berikut.</p>

                        <!-- QR Code Box -->
                        <div class="p-4 bg-white rounded-4 border d-inline-block shadow-sm mb-4 position-relative">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($token) }}" alt="Dynamic QR Code" class="img-fluid rounded" style="width: 240px; height: 240px;">
                            
                            <div class="mt-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger font-monospace px-3 py-1.5" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Token: {{ substr($token, 0, 18) }}...
                                </span>
                            </div>
                        </div>

                        <!-- Timer Progress -->
                        <div class="col-md-8 mx-auto mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Anti-Cheat Refresh:</span>
                                <strong id="timerText" class="text-primary font-monospace">15 Detik</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div id="timerBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
                            </div>
                        </div>

                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> Dilengkapi validasi WhatsApp Otomatis ke Ortu saat Scan Berhasil.
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- TAB 2: TEACHER SCANS STUDENT ID CARD QR -->
    <div class="tab-pane fade" id="mode-teacher" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="card card-custom p-4 text-center shadow border-0">
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-camera text-primary me-2"></i>Kamera Scanner Kartu Pelajar</h5>
                    <p class="text-muted small mb-3">Arahkan kamera ke QR Code NISN yang ada di bagian belakang Kartu Pelajar siswa.</p>

                    <!-- Camera Viewfinder -->
                    <div id="reader-box" class="bg-dark rounded-3 overflow-hidden shadow-inner mb-3 mx-auto position-relative" style="max-width: 480px; min-height: 280px;">
                        <div id="reader" style="width: 100%;"></div>
                    </div>

                    <!-- Manual Input Fallback -->
                    <div class="row justify-content-center mb-3">
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-barcode text-secondary"></i></span>
                                <input type="text" id="manualNisnInput" class="form-control" placeholder="Atau ketik NISN manual jika kamera bermasalah...">
                                <button class="btn btn-primary" type="button" id="btnSubmitNisn">
                                    <i class="fa-solid fa-check me-1"></i> Submit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Scan Feedback Result Box -->
                    <div id="scanResultCard" class="alert alert-success d-none text-start p-3 rounded-3 shadow-sm border-2 border-success">
                        <div class="d-flex align-items-center gap-3">
                            <div id="studentAvatar" class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px; flex-shrink:0;">
                                👤
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark" id="resultName">-</h6>
                                <div class="small text-muted mb-1">
                                    <span id="resultNisn">NISN: -</span> | Kelas: <strong id="resultClass" class="text-dark">-</strong>
                                </div>
                                <div class="badge bg-success text-white px-2 py-1 small">
                                    <i class="fa-solid fa-circle-check me-1"></i> <span id="resultStatus">Hadir & WA Notifikasi Terkirim!</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="scanErrorCard" class="alert alert-danger d-none text-start p-3 rounded-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <span id="errorMessage">Gagal memproses presensi.</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- HTML5-QRCode Scanner Library CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

<script>
    // Tab 1 Dynamic Refresh
    let countdown = 15;
    const timerText = document.getElementById('timerText');
    const timerBar = document.getElementById('timerBar');

    if (timerText && timerBar) {
        setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                location.reload();
            } else {
                timerText.innerText = countdown + ' Detik';
                timerBar.style.width = (countdown / 15 * 100) + '%';
            }
        }, 1000);
    }

    // Tab 2 Teacher Camera Scanner
    let html5QrcodeScanner = null;
    let isProcessingScan = false;

    document.getElementById('mode-teacher-tab').addEventListener('shown.bs.tab', function () {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            });
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
    });

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessingScan) return;
        isProcessingScan = true;

        processStudentAbsen(decodedText.trim());

        setTimeout(() => {
            isProcessingScan = false;
        }, 2500);
    }

    function onScanFailure(error) {
        // Silently ignore scan frame errors
    }

    document.getElementById('btnSubmitNisn').addEventListener('click', function() {
        const nisn = document.getElementById('manualNisnInput').value.trim();
        if (nisn) {
            processStudentAbsen(nisn);
        }
    });

    document.getElementById('manualNisnInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const nisn = this.value.trim();
            if (nisn) processStudentAbsen(nisn);
        }
    });

    function processStudentAbsen(nisn) {
        const scheduleItemId = document.getElementById('activeScheduleId') ? document.getElementById('activeScheduleId').value : null;
        const resultCard = document.getElementById('scanResultCard');
        const errorCard = document.getElementById('scanErrorCard');

        fetch('{{ route("attendance.scan-student") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nisn: nisn,
                schedule_item_id: scheduleItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                errorCard.classList.add('d-none');
                resultCard.classList.remove('d-none');
                
                document.getElementById('resultName').innerText = data.student_name;
                document.getElementById('resultNisn').innerText = 'NISN: ' + data.student_nisn;
                document.getElementById('resultClass').innerText = data.class;
                
                if (data.photo) {
                    document.getElementById('studentAvatar').innerHTML = `<img src="${data.photo}" class="rounded-circle w-100 h-100" style="object-fit:cover;">`;
                } else {
                    document.getElementById('studentAvatar').innerText = '👤';
                }

                // Play Audio BEEP notification
                try {
                    let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    let osc = audioCtx.createOscillator();
                    osc.type = "sine";
                    osc.frequency.value = 880;
                    osc.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.15);
                } catch(e){}

            } else {
                resultCard.classList.add('d-none');
                errorCard.classList.remove('d-none');
                document.getElementById('errorMessage').innerText = data.message || 'NISN tidak valid.';
            }
        })
        .catch(err => {
            resultCard.classList.add('d-none');
            errorCard.classList.remove('d-none');
            document.getElementById('errorMessage').innerText = 'Terjadi kesalahan server saat mencatat presensi.';
        });
    }
</script>
@endsection
