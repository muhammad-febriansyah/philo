@extends('layouts.admin')

@section('title', 'Edit Paket Foto')
@section('page-title', 'Edit Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket Foto</a></li>
    <li class="breadcrumb-item active">Edit: {{ $package->name }}</li>
@endsection

@push('styles')
@include('packages._form-styles')
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('packages.update', $package) }}" method="POST">
            @csrf
            @method('PUT')
            @include('packages._form', ['package' => $package, 'templates' => $templates])
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('packages._form-scripts')
@endpush
