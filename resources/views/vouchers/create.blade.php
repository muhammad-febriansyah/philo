@extends('layouts.admin')

@section('title', 'Buat Voucher')
@section('page-title', 'Buat Voucher')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Voucher</a></li>
    <li class="breadcrumb-item active">Buat Voucher</li>
@endsection

@section('content')
<form method="POST" action="{{ route('vouchers.store') }}" id="voucher-form" data-redirect="{{ route('vouchers.index') }}">
    @csrf
    @include('vouchers._form')
</form>
@endsection
