@extends('layouts.admin')

@section('title', 'Printer')
@section('page-title', 'Printer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Printer</li>
@endsection

@section('content')
@php
    $printersByBranch = $printers->groupBy('branch_id');
    $isAdmin = auth()->user()->isAdmin();
@endphp

<div class="card border-0 shadow-sm mb-3" style="border-top: 3px solid #C9A800 !important;">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center justify-content-center rounded-2"
                  style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                <i class="fas fa-print" style="color:#C9A800;font-size:14px;"></i>
            </span>
            <h5 class="card-title mb-0 fw-semibold">Daftar Printer Cabang</h5>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if ($isAdmin)
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchFilter == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('printers.create') }}" class="btn btn-sm waves-effect"
               style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                <i class="fas fa-plus me-1"></i> Tambah Printer
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($printers->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-print fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">Belum ada printer terdaftar</h6>
                <p class="text-muted small mb-3">
                    Tambahkan printer untuk tiap cabang. Tiap cabang punya printer Voucher Thermal &amp; Foto Photobooth sendiri.
                </p>
                <a href="{{ route('printers.create') }}" class="btn waves-effect"
                   style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                    <i class="fas fa-plus me-1"></i> Tambah Printer
                </a>
            </div>
        @else
            @foreach ($printersByBranch as $branchId => $group)
                @php $branch = $group->first()->branch; @endphp
                <div class="mb-4">
                    <h6 class="fw-semibold mb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-store text-warning"></i>
                        {{ $branch?->name ?? 'Cabang #'.$branchId }}
                        @if ($branch?->code)
                            <span class="badge bg-light text-dark small">{{ $branch->code }}</span>
                        @endif
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="160">Slot</th>
                                    <th>Nama / Catatan</th>
                                    <th width="170">Tipe Koneksi</th>
                                    <th>Device</th>
                                    <th width="120">Profil</th>
                                    <th width="80">Status</th>
                                    <th width="220">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group as $printer)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $printer->purpose === 'voucher' ? 'bg-info' : 'bg-primary' }}">
                                                <i class="fas fa-{{ $printer->purpose === 'voucher' ? 'ticket-alt' : 'camera-retro' }} me-1"></i>
                                                {{ $printer->purposeLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $printer->name ?: '—' }}</td>
                                        <td><small class="text-muted">{{ $printer->connectorLabel() }}</small></td>
                                        <td><code>{{ $printer->device }}</code></td>
                                        <td><span class="badge bg-light text-dark">{{ $printer->profile }}</span></td>
                                        <td>
                                            @if ($printer->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-test"
                                                    data-url="{{ route('printers.test', $printer) }}" title="Test Print">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                            <a href="{{ route('printers.edit', $printer) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-name="{{ $printer->purposeLabel() }} - {{ $branch?->name }}"
                                                    data-url="{{ route('printers.destroy', $printer) }}" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    }

    $(document).on('click', '.btn-test', function () {
        var $btn = $(this);
        var url = $btn.data('url');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: url,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
        .done(function (res) {
            Swal.fire({ icon: 'success', title: 'Test Print', text: res.message, timer: 1800, showConfirmButton: false });
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Test print gagal.';
            Swal.fire({ icon: 'error', title: 'Test print gagal', text: msg });
        })
        .always(function () { $btn.prop('disabled', false).html(originalHtml); });
    });

    $(document).on('click', '.btn-delete', function () {
        var name = $(this).data('name');
        var url = $(this).data('url');
        Swal.fire({
            title: 'Hapus Printer?',
            html: '<strong>' + name + '</strong> akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: csrfToken() },
                success: function () { window.location.reload(); },
                error: function () { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus printer.' }); }
            });
        });
    });
});
</script>
@endpush
