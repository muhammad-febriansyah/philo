@extends('layouts.admin')

@section('title', 'Sesi Foto')
@section('page-title', 'Sesi Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Sesi Foto</li>
@endsection

@section('content')
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

<!-- Modal Gallery -->
<div class="modal fade" id="modal-gallery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs me-2">
                        <span class="avatar-title bg-info bg-opacity-10 text-info rounded-circle font-size-18">
                            <i class="mdi mdi-image-multiple"></i>
                        </span>
                    </div>
                    <h5 class="modal-title mb-0">Hasil Foto - <span id="gallery-order-id" class="text-primary fw-bold"></span></h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4 g-3 align-items-center bg-light bg-opacity-50 p-2 rounded mx-0">
                    <div class="col-md-6 border-end border-light">
                        <div class="d-flex align-items-center">
                            <div class="me-4 border-end border-light pe-4">
                                <p class="text-muted small fw-bold text-uppercase mb-1">CABANG</p>
                                <h6 class="mb-0" id="gallery-branch"></h6>
                            </div>
                            <div>
                                <p class="text-muted small fw-bold text-uppercase mb-1">TEMPLATE / FRAME</p>
                                <h6 class="mb-0" id="gallery-template"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div id="final-image-container" class="d-none">
                            <a href="" id="btn-download-final" class="btn btn-success waves-effect waves-light shadow-sm btn-sm px-3" download>
                                <i class="mdi mdi-download-lock me-1"></i> Download Hasil Cetak
                            </a>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <h6 class="text-dark fw-bold mb-0">Foto Captures (Original)</h6>
                    <div class="ms-auto">
                        <span class="badge bg-light text-dark fw-normal border" id="photo-count-badge">0 Foto</span>
                    </div>
                </div>

                <div id="photos-grid" class="row g-3">
                    <!-- Photos will be injected here -->
                </div>
                
                <div id="no-photos" class="text-center py-5 d-none">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-light text-muted rounded-circle font-size-36">
                            <i class="mdi mdi-image-off-outline"></i>
                        </div>
                    </div>
                    <h6 class="text-dark fw-bold">Tidak Ada Foto Hasil</h6>
                    <p class="text-muted">Sesi ini mungkin belum diselesaikan atau tidak ada foto yang tersimpan.</p>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light bg-opacity-50">
                <button type="button" class="btn btn-secondary px-4 waves-effect rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function () {
    var table = $('#sessions-table').DataTable({
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

    $('#sessions-table').on('click', '.btn-view', function() {
        var id = $(this).data('id');
        $.get('{{ route("photo-sessions.index") }}/' + id, function(data) {
            $('#gallery-order-id').text(data.transaction ? data.transaction.order_id : '-');
            $('#gallery-branch').text(data.branch ? data.branch.name : '-');
            $('#gallery-template').text(data.template ? data.template.name : '-');
            
            if (data.final_image_path) {
                $('#final-image-container').removeClass('d-none');
                $('#btn-download-final').attr('href', '{{ asset("storage") }}/' + data.final_image_path);
            } else {
                $('#final-image-container').addClass('d-none');
            }

            var grid = $('#photos-grid');
            grid.empty();
            
            if (data.photos && data.photos.length > 0) {
                $('#no-photos').addClass('d-none');
                $('#photo-count-badge').text(data.photos.length + ' Foto');
                data.photos.forEach(function(photo) {
                    var downloadUrl = '{{ route("photos.download", ":id") }}'.replace(':id', photo.id);
                    var html = `
                        <div class="col-md-3 col-sm-6">
                            <div class="card h-100 shadow-sm border-0 overflow-hidden bg-light">
                                <div class="position-relative">
                                    <img src="{{ asset("storage") }}/${photo.original_path}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-dark bg-opacity-50">#${photo.order}</span>
                                    </div>
                                </div>
                                <div class="card-body p-2 text-center">
                                    <a href="${downloadUrl}" class="btn btn-sm btn-primary w-100 waves-effect waves-light mt-1">
                                        <i class="mdi mdi-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.append(html);
                });
            } else {
                $('#photo-count-badge').text('0 Foto');
                $('#no-photos').removeClass('d-none');
            }
            
            $('#modal-gallery').modal('show');
        });
    });
});
</script>
@endpush
