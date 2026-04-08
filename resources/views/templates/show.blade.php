@extends('layouts.admin')

@section('title', 'Detail Template — ' . $template->name)
@section('page-title', 'Detail Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Template / Frame</a></li>
    <li class="breadcrumb-item active">{{ $template->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="row g-4">

            {{-- Frame Preview Card --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <div class="bg-light rounded-3 p-3 mb-3 d-flex align-items-center justify-content-center" style="min-height: 240px;">
                            @if($template->frame_url)
                                <img src="{{ $template->frame_url }}" alt="Frame" class="img-fluid rounded-2" style="max-height: 220px; object-fit: contain;">
                            @else
                                <div class="text-muted">
                                    <i class="mdi mdi-image-frame" style="font-size: 4rem; opacity:.3;"></i>
                                    <p class="small mt-2 mb-0">Tidak ada frame</p>
                                </div>
                            @endif
                        </div>
                        <span class="badge bg-light text-muted border font-size-12">Frame / Overlay</span>

                        @if($template->thumbnail_url)
                            <hr class="my-3">
                            <div>
                                <img src="{{ $template->thumbnail_url }}" alt="Thumbnail" class="rounded-2 border shadow-sm" style="max-height: 80px;">
                                <div class="text-muted small mt-1">Thumbnail</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info + Slot Positions --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold">Informasi Template</h6>
                        <span class="{{ $template->is_active ? 'badge bg-success' : 'badge bg-danger' }}">
                            {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless text-dark mb-0">
                            <tr>
                                <th width="160" class="text-muted fw-normal">Nama Template</th>
                                <td class="fw-bold">: {{ $template->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Ukuran Cetak</th>
                                <td>: {{ $template->print_size }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Jumlah Slot Foto</th>
                                <td>: <span class="badge bg-primary">{{ $template->photo_slots }} foto</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Dibuat</th>
                                <td>: {{ $template->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Diperbarui</th>
                                <td>: {{ $template->updated_at->format('d M Y, H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0 fw-bold">Posisi Slot Foto</h6>
                    </div>
                    <div class="card-body">
                        @if($template->slot_positions && count($template->slot_positions) > 0)
                            <div class="vstack gap-2">
                                @foreach($template->slot_positions as $i => $slot)
                                    <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-2 border font-size-13">
                                        <span class="badge bg-primary rounded-pill">{{ $i + 1 }}</span>
                                        <div><span class="text-muted">X:</span> <strong>{{ $slot['x'] }}</strong></div>
                                        <div><span class="text-muted">Y:</span> <strong>{{ $slot['y'] }}</strong></div>
                                        <div><span class="text-muted">W:</span> <strong>{{ $slot['width'] }}</strong></div>
                                        <div><span class="text-muted">H:</span> <strong>{{ $slot['height'] }}</strong></div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 small"><i class="mdi mdi-information-outline me-1"></i>Belum ada posisi slot yang dikonfigurasi.</p>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('templates.edit', $template) }}" class="btn btn-warning waves-effect waves-light px-4">
                        <i class="mdi mdi-pencil me-1"></i> Edit Template
                    </a>
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary waves-effect px-4">
                        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
