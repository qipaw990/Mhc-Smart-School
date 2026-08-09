<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian CBT - {{ $exam->title }}</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8fafc;
            user-select: none;
            -webkit-user-select: none;
        }
        .cbt-topbar {
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-box {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        .nav-box.active {
            box-shadow: 0 0 0 3px #0284c7;
        }
        .nav-box.answered {
            background-color: #10b981;
            color: #fff;
        }
        .nav-box.doubtful {
            background-color: #f59e0b;
            color: #fff;
        }
        .nav-box.unanswered {
            background-color: #e2e8f0;
            color: #475569;
        }
        .option-item {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            transition: all 0.2s ease;
            background: #fff;
        }
        .option-item:hover {
            border-color: #0284c7;
            background: #f0f9ff;
        }
        .option-item.selected {
            border-color: #0284c7;
            background: #e0f2fe;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- CBT Topbar -->
    <div class="cbt-topbar d-flex justify-content-between align-items-center shadow">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-graduation-cap text-primary fs-3"></i>
            <div>
                <div class="fw-bold fs-6">{{ $exam->title }}</div>
                <div class="small text-secondary">{{ $student->name }} ({{ $student->nisn }}) | {{ $student->currentClass?->name }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="bg-dark px-3 py-1.5 rounded-3 border border-secondary d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock text-warning"></i>
                <span class="small text-muted">Sisa Waktu:</span>
                <span id="countdownTimer" class="font-monospace fw-bold fs-5 text-warning">00:00:00</span>
            </div>
            <button onclick="confirmSubmitExam()" class="btn btn-success btn-sm fw-bold px-3">
                <i class="fa-solid fa-paper-plane me-1"></i> Selesai Ujian
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="container-fluid p-4">
        <div class="row g-4">
            <!-- Left: Question Panel -->
            <div class="col-lg-8">
                @foreach($questions as $index => $q)
                    @php
                        $savedAns = $existingAnswers->get($q->id);
                        $chosenValue = $savedAns?->answer_json['selected'] ?? null;
                        $isDoubtful = $savedAns?->is_doubtful ?? false;
                    @endphp
                    <div class="card p-4 shadow-sm border-0 rounded-3 question-card {{ $index === 0 ? '' : 'd-none' }}" id="questionCard_{{ $index }}" data-qid="{{ $q->id }}" data-type="{{ $q->type }}">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <span class="badge bg-primary fs-6 px-3 py-1.5 fw-bold">Soal Nomor {{ $index + 1 }} dari {{ $questions->count() }}</span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $q->type_label }}</span>
                                <span class="badge bg-light text-dark border">Bobot: {{ $q->score_weight }} Poin</span>
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="fs-5 text-dark mb-4" style="line-height: 1.6;">
                            {!! nl2br(e($q->question_text)) !!}
                        </div>

                        @if($q->code_snippet)
                            <div class="p-3 bg-dark text-light rounded-3 font-monospace small mb-4" style="white-space: pre-wrap;">
{{ $q->code_snippet }}
                            </div>
                        @endif

                        <!-- Options / Answer Area -->
                        @if($q->type === 'pg' || $q->type === 'true_false')
                            <div class="vstack gap-3 mb-4">
                                @foreach($q->options as $opt)
                                    <div class="option-item d-flex align-items-center {{ $chosenValue === $opt->option_label ? 'selected' : '' }}" onclick="selectOption({{ $index }}, '{{ $q->id }}', '{{ $opt->option_label }}')">
                                        <span class="badge bg-secondary text-white fw-bold me-3 font-monospace fs-6 px-2.5 py-1.5">
                                            {{ $opt->option_label }}
                                        </span>
                                        <span class="fs-6">{{ $opt->option_text }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($q->type === 'essay')
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Tuliskan Jawaban Uraian Anda:</label>
                                <textarea class="form-control" rows="5" placeholder="Ketik jawaban lengkap di sini..." onchange="saveEssayAnswer({{ $index }}, '{{ $q->id }}', this.value)">{{ $chosenValue }}</textarea>
                            </div>
                        @endif

                        <!-- Action Bar -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <button type="button" class="btn btn-outline-secondary px-3" onclick="prevQuestion({{ $index }})" {{ $index === 0 ? 'disabled' : '' }}>
                                <i class="fa-solid fa-arrow-left me-1"></i> Soal Sebelumnya
                            </button>

                            <button type="button" class="btn {{ $isDoubtful ? 'btn-warning' : 'btn-outline-warning' }} text-dark px-3 fw-bold" id="btnDoubtful_{{ $index }}" onclick="toggleDoubtful({{ $index }}, '{{ $q->id }}')">
                                <i class="fa-solid fa-flag me-1"></i> Ragu-Ragu
                            </button>

                            @if($index < $questions->count() - 1)
                                <button type="button" class="btn btn-primary px-3" onclick="nextQuestion({{ $index }})">
                                    Soal Selanjutnya <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-success px-4 fw-bold" onclick="confirmSubmitExam()">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Selesai Ujian
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right: Question Navigator Grid -->
            <div class="col-lg-4">
                <div class="card p-4 shadow-sm border-0 rounded-3 sticky-top" style="top: 80px;">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-table-cells text-primary me-2"></i>Nomor Soal:</h6>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @foreach($questions as $index => $q)
                            @php
                                $savedAns = $existingAnswers->get($q->id);
                                $hasAnswer = !empty($savedAns?->answer_json['selected']);
                                $isDoubt = $savedAns?->is_doubtful ?? false;
                                $statusClass = $isDoubt ? 'doubtful' : ($hasAnswer ? 'answered' : 'unanswered');
                            @endphp
                            <div class="nav-box {{ $statusClass }} {{ $index === 0 ? 'active' : '' }}" id="navBox_{{ $index }}" onclick="jumpToQuestion({{ $index }})">
                                {{ $index + 1 }}
                            </div>
                        @endforeach
                    </div>

                    <div class="border-top pt-3 small text-muted">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-success">&nbsp;&nbsp;</span> Sudah Dijawab
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning">&nbsp;&nbsp;</span> Ragu-Ragu
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary opacity-50">&nbsp;&nbsp;</span> Belum Dijawab
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="submitExamForm" action="{{ route('cbt.portal.submit', $exam->id) }}" method="POST" class="d-none">
        @csrf
    </form>

    <script>
        let currentQuestion = 0;
        const totalQuestions = {{ $questions->count() }};
        let remainingSeconds = {{ $remainingSeconds }};
        let isDoubtfulState = {};

        // Countdown Timer
        function updateTimer() {
            if (remainingSeconds <= 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Waktu Ujian Telah Habis!',
                    text: 'Lembar jawaban Anda sedang dikumpulkan secara otomatis...',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
                document.getElementById('submitExamForm').submit();
                return;
            }

            const h = String(Math.floor(remainingSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((remainingSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(remainingSeconds % 60).padStart(2, '0');
            document.getElementById('countdownTimer').innerText = `${h}:${m}:${s}`;
            remainingSeconds--;
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // Navigation
        function showQuestion(idx) {
            document.querySelectorAll('.question-card').forEach(c => c.classList.add('d-none'));
            document.querySelectorAll('.nav-box').forEach(n => n.classList.remove('active'));
            
            document.getElementById('questionCard_' + idx).classList.remove('d-none');
            document.getElementById('navBox_' + idx).classList.add('active');
            currentQuestion = idx;
        }

        function nextQuestion(idx) {
            if (idx < totalQuestions - 1) showQuestion(idx + 1);
        }

        function prevQuestion(idx) {
            if (idx > 0) showQuestion(idx - 1);
        }

        function jumpToQuestion(idx) {
            showQuestion(idx);
        }

        // Answer Handling
        function selectOption(idx, questionId, optionLabel) {
            const card = document.getElementById('questionCard_' + idx);
            card.querySelectorAll('.option-item').forEach(o => o.classList.remove('selected'));
            event.currentTarget.classList.add('selected');

            const nav = document.getElementById('navBox_' + idx);
            if (!isDoubtfulState[idx]) {
                nav.classList.remove('unanswered', 'doubtful');
                nav.classList.add('answered');
            }

            // Realtime Auto-Save API
            fetch("{{ route('cbt.portal.save-answer', $exam->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: optionLabel,
                    is_doubtful: isDoubtfulState[idx] || false
                })
            });
        }

        function saveEssayAnswer(idx, questionId, text) {
            const nav = document.getElementById('navBox_' + idx);
            if (text.trim() !== '') {
                nav.classList.remove('unanswered');
                nav.classList.add('answered');
            }
            fetch("{{ route('cbt.portal.save-answer', $exam->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ question_id: questionId, answer: text })
            });
        }

        function toggleDoubtful(idx, questionId) {
            isDoubtfulState[idx] = !isDoubtfulState[idx];
            const btn = document.getElementById('btnDoubtful_' + idx);
            const nav = document.getElementById('navBox_' + idx);

            if (isDoubtfulState[idx]) {
                btn.classList.replace('btn-outline-warning', 'btn-warning');
                nav.classList.remove('unanswered', 'answered');
                nav.classList.add('doubtful');
            } else {
                btn.classList.replace('btn-warning', 'btn-outline-warning');
                nav.classList.remove('doubtful');
                nav.classList.add('answered');
            }
        }

        // Anti-Cheat: Tab Switch & Window Blur Detection
        let isFocused = true;
        window.onblur = function () {
            if (!isFocused) return;
            isFocused = false;

            fetch("{{ route('cbt.portal.tab-switch', $exam->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(r => r.json()).then(data => {
                if (data.is_blocked) {
                    Swal.fire({
                        icon: 'error',
                        title: 'AKSES UJIAN DIBLOKIR!',
                        text: 'Anda telah melebihi batas toleransi berpindah tab/layar browser. Silakan lapor kepada pengawas ruangan.',
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Anti-Cheat!',
                        text: `Terdeteksi keluar dari layar ujian (${data.switch_count} dari ${data.max_allowed}x toleransi).`,
                        confirmButtonText: 'Kembali ke Ujian'
                    }).then(() => { isFocused = true; });
                }
            });
        };

        window.onfocus = function () { isFocused = true; };

        // Submit Confirm
        function confirmSubmitExam() {
            Swal.fire({
                title: 'Kumpulkan Ujian Sekarang?',
                text: 'Pastikan seluruh jawaban telah Anda periksa dengan teliti sebelum mengirim lembar ujian.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Selesai & Kumpulkan',
                cancelButtonText: 'Periksa Kembali'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('submitExamForm').submit();
                }
            });
        }

        // Prevent Copy, Cut, Paste, Right Click & Inspect DevTools
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());

        document.addEventListener('keydown', function (e) {
            // Block F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S
            if (
                e.key === 'F12' ||
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) ||
                (e.ctrlKey && (e.key === 'u' || e.key === 'U' || e.key === 's' || e.key === 'S'))
            ) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
