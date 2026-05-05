@extends('layouts.admin')

@section('title', 'Sesi Foto')
@section('page-title', 'Sesi Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sesi Foto</li>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-alert-circle-outline me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 fw-semibold">Daftar Sesi Foto</h5>
                @if(auth()->user()->isCabang())
                    <a class="btn btn-primary waves-effect" href="{{ url('booth/' . $branches->first()->code) }}" target="_blank">
                        <i class="mdi mdi-monitor me-1"></i> Buka Booth
                    </a>
                @else
                    <div class="dropdown">
                        <button class="btn btn-primary waves-effect dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="mdi mdi-monitor me-1"></i> Buka Booth
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            @forelse($branches as $branch)
                                <li>
                                    <a class="dropdown-item" href="{{ url('booth/' . $branch->code) }}" target="_blank">
                                        <i class="mdi mdi-store-outline me-2 text-muted"></i>{{ $branch->name }}
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item text-muted">Tidak ada cabang aktif</span></li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sessions-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Order ID</th>
                                <th>Cabang</th>
                                <th>Template</th>
                                <th width="90">Status</th>
                                <th width="150">Waktu Selesai</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function () {
    $('#sessions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("photo-sessions.data") }}',
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'order_id', name: 'transaction.order_id'},
            {data: 'branch_name', name: 'branch.name'},
            {data: 'template_name', name: 'template.name'},
            {data: 'status_badge', name: 'status', className: 'text-center'},
            {data: 'completed_at', name: 'completed_at', render: function(data) {
                return data ? '<span class="text-muted fw-bold font-size-12"><i class="mdi mdi-check-circle-outline text-success me-1"></i>' + moment(data).format('DD/MM/YYYY HH:mm') + '</span>' : '<span class="text-muted">-</span>';
            }},
            {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center'},
        ],
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { first: 'Pertama', last: 'Terakhir', next: '&raquo;', previous: '&laquo;' },
        },
        responsive: true,
        order: [[5, 'desc']]
    });
});
</script>
@endpush
