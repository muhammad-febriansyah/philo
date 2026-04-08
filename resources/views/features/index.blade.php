@extends('layouts.admin')

@section('title', 'Fitur Unggulan')
@section('page-title', 'Fitur Unggulan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Fitur Unggulan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Fitur Unggulan</h5>
                <button type="button" class="btn btn-primary btn-sm waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah Fitur
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="features-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th width="60">Icon</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th width="70" class="text-center">Urutan</th>
                                <th width="90" class="text-center">Status</th>
                                <th width="150" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modal-feature" tabindex="-1" aria-labelledby="modal-feature-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-feature-title">Tambah Fitur Unggulan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-feature">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="feature-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Icon <span class="text-danger">*</span></label>
                        <input type="hidden" name="icon" id="feature-icon" required>
                        <button type="button" id="btn-pick-icon"
                                class="btn btn-outline-secondary d-flex align-items-center gap-2 w-100 text-start">
                            <i class="mdi mdi-star fs-5" id="icon-preview-btn"></i>
                            <span id="icon-label-btn" class="text-muted">Pilih icon...</span>
                        </button>
                        <!-- Icon Picker Panel -->
                        <div id="icon-picker-panel" class="border rounded mt-1 p-2 bg-white shadow-sm" style="display:none;">
                            <input type="text" id="icon-search" class="form-control form-control-sm mb-2" placeholder="Cari icon...">
                            <div id="icon-grid" class="d-flex flex-wrap gap-1" style="max-height:220px;overflow-y:auto;"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="feature-title" class="form-control"
                               placeholder="Contoh: Manajemen Sesi Foto" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="feature-description" class="form-control" rows="3"
                                  placeholder="Penjelasan singkat fitur ini..." required maxlength="500"></textarea>
                        <small class="text-muted"><span id="desc-count">0</span>/500 karakter</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="feature-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="feature-active" value="1" checked>
                                <label class="form-check-label" for="feature-active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn-save">
                        <i class="mdi mdi-content-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#features-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("features.data") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'icon_preview', name: 'icon', orderable: false, searchable: false, className: 'text-center' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'sort_order', name: 'sort_order', className: 'text-center' },
            { data: 'status', name: 'is_active', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
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
    });

    // ── Icon Picker ──────────────────────────────────────────────
    var MDI_ICONS = [
        'mdi-camera-outline','mdi-camera','mdi-image','mdi-image-outline','mdi-image-frame',
        'mdi-image-multiple-outline','mdi-filmstrip','mdi-video-outline','mdi-television-play',
        'mdi-credit-card-outline','mdi-credit-card','mdi-cash','mdi-cash-multiple','mdi-wallet-outline',
        'mdi-qrcode','mdi-barcode','mdi-bank-outline','mdi-currency-usd',
        'mdi-store','mdi-store-outline','mdi-storefront-outline','mdi-shop-outline',
        'mdi-chart-line','mdi-chart-bar','mdi-chart-pie','mdi-poll','mdi-trending-up',
        'mdi-account-outline','mdi-account-multiple-outline','mdi-account-group-outline','mdi-account-circle-outline',
        'mdi-shield-check-outline','mdi-shield-outline','mdi-lock-outline','mdi-key-outline','mdi-security',
        'mdi-star-outline','mdi-star','mdi-heart-outline','mdi-thumb-up-outline','mdi-trophy-outline',
        'mdi-settings-outline','mdi-cog-outline','mdi-tune','mdi-tools','mdi-wrench-outline',
        'mdi-bell-outline','mdi-bell','mdi-email-outline','mdi-message-outline','mdi-chat-outline',
        'mdi-map-marker-outline','mdi-map-outline','mdi-earth','mdi-navigation-outline',
        'mdi-clock-outline','mdi-calendar-outline','mdi-calendar-check-outline','mdi-alarm',
        'mdi-check-circle-outline','mdi-close-circle-outline','mdi-information-outline','mdi-alert-outline',
        'mdi-download-outline','mdi-upload-outline','mdi-cloud-outline','mdi-cloud-upload-outline',
        'mdi-folder-outline','mdi-file-outline','mdi-file-document-outline','mdi-printer-outline',
        'mdi-phone-outline','mdi-cellphone','mdi-laptop','mdi-monitor-outline','mdi-tablet-outline',
        'mdi-wifi','mdi-bluetooth','mdi-flash-outline','mdi-lightning-bolt','mdi-power',
        'mdi-magnify','mdi-filter-outline','mdi-sort','mdi-refresh','mdi-sync',
        'mdi-link','mdi-share-variant-outline','mdi-export-variant','mdi-import',
        'mdi-palette-outline','mdi-brush-outline','mdi-pencil-outline','mdi-draw',
        'mdi-package-variant-closed','mdi-tag-outline','mdi-label-outline','mdi-ticket-outline',
        'mdi-gift-outline','mdi-percent-outline','mdi-sale','mdi-coupon-outline',
        'mdi-truck-outline','mdi-car-outline','mdi-motorbike','mdi-bike-outline',
        'mdi-home-outline','mdi-office-building-outline','mdi-city-variant-outline',
        'mdi-leaf-outline','mdi-tree-outline','mdi-flower-outline','mdi-water-outline',
        'mdi-food-outline','mdi-coffee-outline','mdi-silverware-fork-knife',
    ];

    function renderIcons(filter) {
        var grid = $('#icon-grid');
        grid.empty();
        var filtered = filter
            ? MDI_ICONS.filter(function(n){ return n.indexOf(filter) !== -1; })
            : MDI_ICONS;
        $.each(filtered, function(_, name) {
            var fullClass = 'mdi ' + name;
            var btn = $('<button type="button" title="' + name + '">')
                .addClass('btn btn-outline-secondary btn-sm icon-item p-1')
                .css({'width':'36px','height':'36px','font-size':'18px','line-height':'1'})
                .html('<i class="' + fullClass + '"></i>')
                .data('icon', fullClass);
            grid.append(btn);
        });
    }

    function setIcon(fullClass) {
        $('#feature-icon').val(fullClass);
        $('#icon-preview-btn').attr('class', fullClass + ' fs-5');
        $('#icon-label-btn').text(fullClass).removeClass('text-muted');
        $('#icon-picker-panel').hide();
    }

    renderIcons('');

    $('#btn-pick-icon').on('click', function(e) {
        e.stopPropagation();
        var panel = $('#icon-picker-panel');
        panel.toggle();
        if (panel.is(':visible')) { $('#icon-search').val('').trigger('input').focus(); }
    });

    $('#icon-search').on('input', function() {
        renderIcons($(this).val().trim().replace(/^mdi-?/, ''));
    });

    $(document).on('click', '.icon-item', function() {
        setIcon($(this).data('icon'));
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#btn-pick-icon, #icon-picker-panel').length) {
            $('#icon-picker-panel').hide();
        }
    });
    // ─────────────────────────────────────────────────────────────

    // Description character count
    $('#feature-description').on('input', function () {
        $('#desc-count').text($(this).val().length);
    });

    function resetForm() {
        $('#form-feature')[0].reset();
        $('#form-method').val('POST');
        $('#feature-id').val('');
        $('#feature-icon').val('');
        $('#modal-feature-title').text('Tambah Fitur Unggulan');
        $('#icon-preview-btn').attr('class', 'mdi mdi-star fs-5');
        $('#icon-label-btn').text('Pilih icon...').addClass('text-muted');
        $('#icon-picker-panel').hide();
        $('#desc-count').text('0');
        $('.is-invalid').removeClass('is-invalid');
        $('#feature-active').prop('checked', true);
    }

    // Tambah
    $('.btn-add').on('click', function () {
        resetForm();
        $('#modal-feature').modal('show');
    });

    // Edit — data already in data-* attributes
    $('#features-table').on('click', '.btn-edit', function () {
        var btn = $(this);
        resetForm();
        $('#modal-feature-title').text('Edit Fitur Unggulan');
        $('#form-method').val('PUT');
        $('#feature-id').val(btn.data('id'));
        setIcon(btn.data('icon'));
        $('#feature-title').val(btn.data('title'));
        $('#feature-description').val(btn.data('description'));
        $('#desc-count').text(String(btn.data('description')).length);
        $('#feature-sort').val(btn.data('sort_order'));
        $('#feature-active').prop('checked', btn.data('is_active') == '1');
        $('#modal-feature').modal('show');
    });

    // Submit (store & update)
    $('#form-feature').on('submit', function (e) {
        e.preventDefault();
        var id = $('#feature-id').val();
        var url = id
            ? '{{ route("features.index") }}/' + id
            : '{{ route("features.store") }}';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modal-feature').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Fitur unggulan berhasil disimpan.', timer: 2000, showConfirmButton: false });
            },
            error: function (err) {
                if (err.status === 422) {
                    var errors = err.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    Object.keys(errors).forEach(function (key) {
                        $('[name="' + key + '"]').addClass('is-invalid');
                    });
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: 'Mohon periksa kembali inputan Anda.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server.' });
                }
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="mdi mdi-content-save me-1"></i> Simpan');
            },
        });
    });

    // Hapus
    $('#features-table').on('click', '.btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Fitur?',
            html: 'Fitur <strong>' + name + '</strong> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("features.index") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Fitur unggulan berhasil dihapus.', timer: 2000, showConfirmButton: false });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan, silakan coba lagi.' });
                    },
                });
            }
        });
    });
});
</script>
@endpush
