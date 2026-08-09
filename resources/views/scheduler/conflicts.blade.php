@extends('layouts.app')

@section('title', 'Conflict Detector & Explainability Report')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Conflict Detector & Explainability Report</h4>
        <p class="text-muted mb-0 small">Pemeriksaan real-time bentrok guru, bentrok rombel kelas, bentrok ruangan/lab, dan ketersediaan waktu.</p>
    </div>
    <a href="{{ route('scheduler.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Matriks Jadwal
    </a>
</div>

@if(!$activeSchedule)
    <div class="card card-custom p-5 text-center">
        <i class="fa-solid fa-calendar-xmark text-muted mb-3" style="font-size: 3rem;"></i>
        <h5 class="fw-bold">Belum Ada Jadwal Aktif</h5>
        <p class="text-muted small">Silakan generate jadwal sekolah terlebih dahulu melalui Auto Scheduler.</p>
    </div>
@else
    <!-- Status Diagnostic Banner -->
    @if(!$diagnostic['has_conflicts'])
        <div class="alert alert-success border-0 shadow-sm p-4 rounded-3 d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white p-3 rounded-circle">
                    <i class="fa-solid fa-circle-check fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-success mb-1">ZERO CONFLICT DETECTED (100% Sempurna)</h5>
                    <div class="text-dark small">Seluruh jadwal guru, kelas, dan ruangan/lab berada dalam kondisi bersih tanpa ada bentrok ganda.</div>
                </div>
            </div>
            <span class="badge bg-success fs-6 px-3 py-2">Constraints Clean</span>
        </div>
    @else
        <div class="alert alert-danger border-0 shadow-sm p-4 rounded-3 d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-danger text-white p-3 rounded-circle">
                    <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-danger mb-1">DITEMUKAN {{ $diagnostic['total_conflicts'] }} POTENSI BENTROK JADWAL!</h5>
                    <div class="text-dark small">Periksa rincian bentrok di bawah ini beserta rekomendasi otomatis untuk penyelesaiannya.</div>
                </div>
            </div>
            <span class="badge bg-danger fs-6 px-3 py-2">Action Required</span>
        </div>
    @endif

    <div class="row g-4">
        <!-- Diagnostic Details -->
        <div class="col-lg-7">
            <div class="card card-custom p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-magnifying-glass-chart text-primary me-2"></i>Rincian Diagnosis Bentrok:</h5>

                @if(!$diagnostic['has_conflicts'])
                    <div class="p-4 text-center text-muted bg-light rounded-3">
                        <i class="fa-solid fa-shield-check text-success fs-2 mb-2"></i>
                        <p class="mb-0 small">Tidak ditemukan bentrok pada jadwal versi <strong>{{ $activeSchedule->name }}</strong>.</p>
                    </div>
                @else
                    <div class="vstack gap-3">
                        @foreach($diagnostic['teacher_clashes'] as $tc)
                            <div class="p-3 border-start border-danger border-4 bg-light rounded-3">
                                <div class="fw-bold text-danger small"><i class="fa-solid fa-user-xmark me-1"></i> BENTROK GURU: {{ $tc['teacher'] }}</div>
                                <div class="text-dark small mt-1">{{ $tc['message'] }}</div>
                            </div>
                        @endforeach

                        @foreach($diagnostic['class_clashes'] as $cc)
                            <div class="p-3 border-start border-warning border-4 bg-light rounded-3">
                                <div class="fw-bold text-warning small"><i class="fa-solid fa-chalkboard-user me-1"></i> BENTROK KELAS: {{ $cc['class'] }}</div>
                                <div class="text-dark small mt-1">{{ $cc['message'] }}</div>
                            </div>
                        @endforeach

                        @foreach($diagnostic['room_clashes'] as $rc)
                            <div class="p-3 border-start border-info border-4 bg-light rounded-3">
                                <div class="fw-bold text-info small"><i class="fa-solid fa-door-closed me-1"></i> BENTROK RUANGAN: {{ $rc['room'] }}</div>
                                <div class="text-dark small mt-1">{{ $rc['message'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- AI Explainability Recommendations -->
        <div class="col-lg-5">
            <div class="card card-custom p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Rekomendasi Penyelesaian (Explainability):</h5>

                @if(!$diagnostic['has_conflicts'])
                    <div class="alert alert-light border small text-muted">
                        Jadwal sudah teroptimasi dengan baik sesuai Hard Constraints (Guru, Kelas, Ruang) dan Soft Constraints (Blok Praktik 2-4 JP).
                    </div>
                @else
                    <div class="vstack gap-2">
                        @foreach($diagnostic['recommendations'] as $rec)
                            <div class="p-3 bg-light rounded-3 border small">
                                <i class="fa-solid fa-circle-arrow-right text-primary me-2"></i> {{ $rec }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <form action="{{ route('scheduler.generate') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 fw-bold shadow-sm">
                                <i class="fa-solid fa-rotate me-1"></i> Regenerate Jadwal Otomatis
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
