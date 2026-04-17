@extends('layouts.admin')

@section('title', 'FAQ')
@section('page-title', 'FAQ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">FAQ</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Daftar FAQ</h5>
                    <p class="text-muted mb-0">Kelola pertanyaan umum yang akan tampil di halaman publik.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah FAQ
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="faqs-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Pertanyaan</th>
                                <th>Jawaban</th>
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

<div class="modal fade" id="modal-faq" tabindex="-1" aria-labelledby="modal-faq-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-faq-title">Tambah FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-faq">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="faq-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                        <input type="text" name="question" id="faq-question" class="form-control" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                        <textarea name="answer" id="faq-answer" class="form-control" rows="5" maxlength="1200" required></textarea>
                        <small class="text-muted"><span id="answer-count">0</span>/1200 karakter</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="faq-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="faq-active" value="1" checked>
                                <label class="form-check-label" for="faq-active">Aktif</label>
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
    var table = $('#faqs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("faqs.data") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'question', name: 'question' },
            { data: 'answer', name: 'answer', render: function(data) {
                return '<div class="text-wrap" style="max-width:520px;white-space:normal;">' + $('<div>').text(data).html() + '</div>';
            }},
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

    function resetForm() {
        $('#form-faq')[0].reset();
        $('#form-method').val('POST');
        $('#faq-id').val('');
        $('#faq-sort').val('0');
        $('#faq-active').prop('checked', true);
        $('#modal-faq-title').text('Tambah FAQ');
        $('#answer-count').text('0');
        $('.is-invalid').removeClass('is-invalid');
    }

    $('#faq-answer').on('input', function () {
        $('#answer-count').text($(this).val().length);
    });

    $('.btn-add').on('click', function () {
        resetForm();
        $('#modal-faq').modal('show');
    });

    $('#faqs-table').on('click', '.btn-edit', function () {
        var btn = $(this);
        resetForm();
        $('#modal-faq-title').text('Edit FAQ');
        $('#form-method').val('PUT');
        $('#faq-id').val(btn.data('id'));
        $('#faq-question').val(btn.data('question'));
        $('#faq-answer').val(btn.data('answer')).trigger('input');
        $('#faq-sort').val(btn.data('sort_order'));
        $('#faq-active').prop('checked', btn.data('is_active') == '1');
        $('#modal-faq').modal('show');
    });

    $('#form-faq').on('submit', function (e) {
        e.preventDefault();
        var id = $('#faq-id').val();
        var url = id
            ? '{{ route("faqs.index") }}/' + id
            : '{{ route("faqs.store") }}';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function () {
                $('#modal-faq').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'FAQ berhasil disimpan.', timer: 2000, showConfirmButton: false });
            },
            error: function (err) {
                if (err.status === 422) {
                    var errors = err.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    Object.keys(errors).forEach(function (key) {
                        $('[name="' + key + '"]').addClass('is-invalid');
                    });
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: 'Mohon periksa kembali input FAQ.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: 'Tidak dapat menyimpan data FAQ.' });
                }
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="mdi mdi-content-save me-1"></i> Simpan');
            }
        });
    });

    $('#faqs-table').on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus FAQ?',
            text: 'Pertanyaan "' + name + '" akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f46a6a',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '{{ route("faqs.index") }}/' + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function () {
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'FAQ berhasil dihapus.', timer: 1800, showConfirmButton: false });
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'FAQ tidak dapat dihapus.' });
                }
            });
        });
    });
});
</script>
@endpush
