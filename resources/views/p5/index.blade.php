@extends('layouts.app')

@section('title', 'Rapor Projek Profil Pelajar Pancasila (P5)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Rapor Projek Profil Pelajar Pancasila (P5)</h4>
        <p class="text-muted mb-0 small">Pelaksanaan dan penilaian capaian dimensi projek P5 dengan skala <strong>MB (Mulai Berkembang), SB, BSH, dan SAB (Sangat Berkembang)</strong>.</p>
    </div>
    <a href="{{ route('p5.create') }}" class="btn btn-primary btn-sm fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Buat Modul Projek P5 Baru
    </a>
</div>

<div class="row g-4">
    @forelse($projects as $p)
        <div class="col-md-6">
            <div class="card card-custom p-4 h-100 shadow-sm border-2 border-primary position-relative">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Tema: {{ $p->theme }}</span>
                    <span class="badge bg-light text-dark border">{{ $p->schoolClass?->name }}</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $p->title }}</h5>
                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.82rem;">
                    {{ $p->description }}
                </p>

                <div class="p-3 bg-light rounded-3 border small mb-3">
                    <strong class="text-secondary d-block mb-1">Dimensi & Elemen yang Dikembangkan:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($p->dimensions as $dim)
                            <li><strong>{{ $dim->dimension_name }}:</strong> {{ $dim->sub_element }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                    <span class="small text-muted">{{ $p->dimensions->count() }} Dimensi Diuji</span>
                    <div>
                        <a href="{{ route('p5.scores', $p->id) }}" class="btn btn-sm btn-primary fw-bold me-1">
                            <i class="fa-solid fa-table-cells me-1"></i> Input Nilai P5
                        </a>
                        <form action="{{ route('p5.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus projek P5 ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fa-solid fa-hands-holding-child text-muted mb-3" style="font-size: 3rem;"></i>
            <h5 class="fw-bold">Belum Ada Projek P5</h5>
            <p class="small text-muted">Rancang modul projek P5 untuk rombel kelas pada semester ini.</p>
            <a href="{{ route('p5.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> Buat Modul Projek Sekarang
            </a>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $projects->links() }}
</div>
@endsection
