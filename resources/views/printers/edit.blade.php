@extends('layouts.admin')

@section('title', 'Edit Printer')
@section('page-title', 'Edit Printer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('printers.index') }}">Printer</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
@include('printers._form')
@endsection
