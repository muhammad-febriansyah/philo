@extends('layouts.admin')

@section('title', 'Tambah Printer')
@section('page-title', 'Tambah Printer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('printers.index') }}">Printer</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
@include('printers._form')
@endsection
