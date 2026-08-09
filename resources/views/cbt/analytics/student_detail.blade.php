@extends('layouts.app')

@section('title', 'Lembar Jawaban Siswa - ' . $studentExam->student?->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('cbt.exams.analytics', $studentExam->exam_id) }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Rekap Nilai
        </a>
        <h4 class="fw-bold mb-1">Lembar Jawaban: {{ $studentExam->student?->name }}</h4>
        <p class="text-muted mb-0 small">{{ $studentExam->exam?->title }} | Kelas: <strong>{{ $studentExam->student?->currentClass?->name }}</strong> | Skor: <span class="badge bg-success fs-6">{{ $studentExam->total_score }}</span></p>
    </div>
</div>

<div class="vstack gap-4 mb-5">
    @foreach($studentExam->answers as $idx => $ans)
        @php
            $q = $ans->question;
        @endphp
        <div class="card card-custom p-4 border-start {{ $ans->is_correct ? 'border-success' : 'border-danger' }} border-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fw-bold">Nomor {{ $q->order_number }}</span>
                    <span class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $q->type_label }}</span>
                    <span class="badge bg-light text-dark border">Bobot: {{ $q->score_weight }} Poin</span>
                </div>
                <div>
                    @if($q->type === 'essay')
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen-nib me-1"></i> Penilaian Essay Guru</span>
                    @else
                        @if($ans->is_correct)
                            <span class="badge bg-success fs-6"><i class="fa-solid fa-check me-1"></i> Jawaban Benar (+{{ $ans->score_obtained }} Poin)</span>
                        @else
                            <span class="badge bg-danger fs-6"><i class="fa-solid fa-xmark me-1"></i> Jawaban Salah (0 Poin)</span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Question Text -->
            <div class="fs-6 text-dark mb-3">
                {!! nl2br(e($q->question_text)) !!}
            </div>

            <!-- Answer Box -->
            <div class="p-3 bg-light rounded-3 border small mb-3">
                <strong class="text-secondary">Jawaban yang Dipilih Siswa:</strong>
                <div class="mt-1 text-dark fs-6 font-monospace">
                    @if($q->type === 'pg' || $q->type === 'true_false')
                        Opsi [{{ $ans->answer_json['selected'] ?? '-' }}]
                    @elseif($q->type === 'essay')
                        <div class="p-3 bg-white border rounded font-monospace small" style="white-space: pre-wrap;">
{{ $ans->answer_json['selected'] ?? 'Siswa tidak mengisi jawaban essay.' }}
                        </div>
                    @else
                        {{ json_encode($ans->answer_json) }}
                    @endif
                </div>
            </div>

            <!-- Essay Manual Scoring Form -->
            @if($q->type === 'essay')
                <div class="p-3 border rounded-3 bg-light">
                    <form action="{{ route('cbt.analytics.grade-essay', $ans->id) }}" method="POST" class="row g-2 align-items-center">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Beri Nilai (Maks: {{ $q->score_weight }})</label>
                            <input type="number" step="0.5" name="score_obtained" class="form-control form-control-sm" value="{{ $ans->score_obtained }}" min="0" max="{{ $q->score_weight }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Catatan Umpan Balik Guru</label>
                            <input type="text" name="teacher_notes" class="form-control form-control-sm" value="{{ $ans->teacher_notes }}" placeholder="Komentar rubrik pemahaman konsep...">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                <i class="fa-solid fa-save me-1"></i> Simpan Nilai Essay
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection
