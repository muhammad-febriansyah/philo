@extends('layouts.admin')

@section('title', 'Detail Paket Foto')
@section('page-title', 'Detail Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket Foto</a></li>
    <li class="breadcrumb-item active">{{ $package->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Informasi Paket</h5>
                <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted mb-1">Nama Paket</p>
                    <h5 class="mb-0">{{ $package->name }}</h5>
                </div>

                <div class="mb-3">
                    <p class="text-muted mb-1">Deskripsi</p>
                    <p class="mb-0">{{ $package->description ?: '-' }}</p>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <p class="text-muted mb-1">Jumlah Foto</p>
                        <h6 class="mb-0">{{ $package->photo_count }}</h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="text-muted mb-1">Ukuran Cetak</p>
                        <h6 class="mb-0">{{ $package->print_size }}</h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="text-muted mb-1">Harga</p>
                        <h6 class="mb-0">Rp {{ number_format($package->price, 0, ',', '.') }}</h6>
                    </div>
                </div>

                <div>
                    <p class="text-muted mb-2">Template Terkait</p>
                    @if($package->templates->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($package->templates as $template)
                                <span class="badge bg-light text-dark border">
                                    {{ $template->name }} ({{ $template->print_size }} · {{ $template->photo_slots }} slot)
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 text-muted">Belum ada template yang dipilih.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('packages.edit', $package) }}" class="btn btn-warning waves-effect">
                <i class="mdi mdi-pencil me-1"></i> Edit Paket
            </a>
            <a href="{{ route('packages.index') }}" class="btn btn-secondary waves-effect">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
