@extends('layouts.admin')

@section('title', 'Tambah Paket Foto')
@section('page-title', 'Tambah Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket Foto</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@push('styles')
@include('packages._form-styles')
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('packages.store') }}" method="POST">
            @csrf
            @include('packages._form', ['package' => null, 'templates' => $templates])
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('packages._form-scripts')
@endpush
