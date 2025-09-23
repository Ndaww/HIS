@extends('layouts.app')

@section('title', 'Daftar Departemen')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Departemen</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Master </span> / Departemen </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Master Departemen</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#departmentModal" id="add-department-btn">
                Tambah Departemen
            </button>

            <table class="table table-bordered yajra-datatable">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th>Nama Departemen</th>
                        <th>Kepala Departemen</th>
                        {{-- <th>Dibuat Pada</th>
                        <th>Diperbarui Pada</th> --}}
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalLabel">Tambah Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="departmentForm">
                        <input type="hidden" id="department-id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Departemen</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="head_id" class="form-label">Kepala Departemen</label>
                            <select class="form-control" id="head_id" name="head_id">
                                <option value="">-- Pilih Kepala Departemen --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="head_id-error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" form="departmentForm" class="btn btn-primary" id="save-btn">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('.yajra-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master.depts.index') }}",
            lengthMenu: [10, 25, 50, 100],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'head_name', name: 'head_name', orderable: false, searchable: false},
                // {data: 'created_at', name: 'created_at'},
                // {data: 'updated_at', name: 'updated_at'},
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#add-department-btn').click(function() {
            $('#departmentModalLabel').text('Tambah Departemen');
            $('#save-btn').text('Simpan');
            $('#departmentForm').trigger('reset');
            $('#departmentForm').attr('action', '{{ route("master.depts.store") }}');
            $('#department-id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        });

        $(document).on('click', '.edit-btn', function() {
            var departmentId = $(this).data('id');
            var departmentUrl = '{{ route("master.depts.show", ["department" => ":id"]) }}';
            departmentUrl = departmentUrl.replace(':id', departmentId);

            $.get(departmentUrl, function(data) {
                $('#departmentModalLabel').text('Edit Departemen');
                $('#save-btn').text('Perbarui');

                $('#department-id').val(data.id);
                $('#name').val(data.name);
                $('#head_id').val(data.head_id);

                var updateUrl = '{{ route("master.depts.update", ["department" => ":id"]) }}';
                updateUrl = updateUrl.replace(':id', data.id);
                $('#departmentForm').attr('action', updateUrl);

                $('#departmentForm').append('<input type="hidden" name="_method" value="PUT">');

                $('#departmentModal').modal('show');
            });
        });

        $('#departmentForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var method = form.find('input[name="_method"]').val() || 'POST';

            $.ajax({
                type: method,
                url: url,
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#departmentModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.success,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    $.each(errors, function(key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key + '-error').text(value);
                    });
                }
            });
        });

        $(document).on('click', '.delete-btn', function() {
            var departmentId = $(this).data('id');
            var deleteUrl = '{{ route("master.depts.destroy", ["department" => ":id"]) }}';
            deleteUrl = deleteUrl.replace(':id', departmentId);

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'DELETE',
                        url: deleteUrl,
                        dataType: 'json',
                        success: function(response) {
                            table.ajax.reload();
                            Swal.fire({
                                title: 'Terhapus!',
                                text: response.success,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    });
                }
            });
        });

        $('#departmentModal').on('hidden.bs.modal', function () {
            $('#departmentForm').find('input[name="_method"]').remove();
        });
    });
</script>
@endsection
