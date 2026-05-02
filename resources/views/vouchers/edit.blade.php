@extends('layouts.admin')

@section('title', 'Edit Voucher')
@section('page-title', 'Edit Voucher')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Voucher</a></li>
    <li class="breadcrumb-item active">{{ $voucher->code }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('vouchers.update', $voucher) }}" id="voucher-form" data-redirect="{{ route('vouchers.index') }}">
    @csrf
    @method('PUT')
    @include('vouchers._form')
</form>
@endsection
