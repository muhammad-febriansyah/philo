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

            {{-- Frame Preview --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0 fw-bold">Preview Frame</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center p-4" style="min-height: 280px;">
                        @php $imgUrl = $template->thumbnail_url ?? $template->frame_url; @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $template->name }}"
                                 class="img-fluid rounded-2 shadow-sm" style="max-height: 260px; object-fit: contain;">
                        @else
                            <div class="text-center text-muted">
                                <i class="mdi mdi-image-frame" style="font-size: 4rem; opacity:.25;"></i>
                                <p class="small mt-2 mb-0">Belum ada gambar</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info + Slots --}}
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold">Informasi Template</h6>
                        @if($template->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th width="160" class="text-muted fw-normal ps-3">Nama Template</th>
                                    <td class="fw-bold">{{ $template->name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal ps-3">Ukuran Cetak</th>
                                    <td>
                                        @php
                                            $sizes = ['strip' => 'Photo Strip (2×6")', '4R' => '4R', 'A4' => 'A4', 'A3' => 'A3'];
                                        @endphp
                                        <span class="badge bg-light text-dark border">{{ $sizes[$template->print_size] ?? $template->print_size }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal ps-3">Jumlah Slot Foto</th>
                                    <td><span class="badge bg-primary">{{ $template->photo_slots }} foto</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal ps-3">Dibuat</th>
                                    <td>{{ $template->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal ps-3">Diperbarui</th>
                                    <td>{{ $template->updated_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('templates.edit', $template) }}" class="btn btn-warning waves-effect waves-light px-4">
                        <i class="mdi mdi-pencil me-1"></i> Edit Template
                    </a>
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary waves-effect px-4">
                        <i class="mdi mdi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
