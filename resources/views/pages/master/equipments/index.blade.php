@extends('layouts.app')
@section('title','Master - Equipment')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Master Equipment</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Master </span> / <span> Master Equipment </span></div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title">Master Equipment Type</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-primary my-2" id="create-new-type">Tambah Tipe</button>
            <table class="table table-bordered yajra-datatable" id="master-equipment-type-table">
                <thead>
                    <tr>
                        <th width="6%">No</th>
                        <th>Nama</th>
                        {{-- <th>Dibuat Pada</th>
                        <th>Diperbarui Pada</th> --}}
                        <th width="150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title">Master Equipment</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-primary my-2" id="create-new-equipment">Tambah Equipment</button>
            <table class="table table-bordered yajra-datatable" id="master-equipment-table">
                <thead>
                    <tr>
                        <th width="6%">No</th>
                        <th>Nama</th>
                        <th>Nomor Seri</th>
                        <th>ID Ruangan</th>
                        <th>Tipe Equipment</th>
                        {{-- <th>Dibuat Pada</th>
                        <th>Diperbarui Pada</th> --}}
                        <th width="150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Master Equipment Type -->
    <div class="modal fade" id="ajax-type-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="type-modal-heading"></h4>
                    <button type="button" class="close btn btn-secondary" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="type-form" name="type-form" class="form-horizontal">
                        <input type="hidden" name="type_id" id="type_id">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Nama</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="type_name" name="name" placeholder="Masukkan Nama" value="" maxlength="50" required="">
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-10 mt-3">
                            <button type="submit" class="btn btn-primary" id="save-type-btn" value="create">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Master Equipment -->
    <div class="modal fade" id="ajax-equipment-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="equipment-modal-heading"></h4>
                    <button type="button" class="close btn btn-secondary" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="equipment-form" name="equipment-form" class="form-horizontal">
                        <input type="hidden" name="equipment_id" id="equipment_id">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Nama</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="equipment_name" name="name" placeholder="Masukkan Nama" value="" maxlength="50" required="">
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label for="serial_number" class="col-sm-2 control-label">Nomor Seri</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Masukkan Nomor Seri" value="" maxlength="50" required="">
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label for="room_id" class="col-sm-2 control-label">ID Ruangan</label>
                            <div class="col-sm-12">
                                <select class="form-select" name="room_id" id="room_id" required>
                                    @foreach ($rooms as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label for="equipment_type_id" class="col-sm-2 control-label">Tipe Equipment</label>
                            <div class="col-sm-12">
                                <select class="form-control" id="equipment_type_id" name="equipment_type_id" required="">
                                    @foreach($equipmentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-10 mt-3">
                            <button type="submit" class="btn btn-primary" id="save-equipment-btn" value="create">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Datatable for Master Equipment Type
        var typeTable = $('#master-equipment-type-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-equipment-type.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                // {data: 'created_at', name: 'created_at'},
                // {data: 'updated_at', name: 'updated_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Add New Equipment Type
        $('#create-new-type').click(function () {
            $('#save-type-btn').val("create-type");
            $('#type_id').val('');
            $('#type-form').trigger("reset");
            $('#type-modal-heading').html("Tambah Tipe Equipment Baru");
            $('#ajax-type-modal').modal('show');
        });

        // Edit Equipment Type
        $('body').on('click', '.edit-type', function () {
            var type_id = $(this).data('id');
            $.get("{{ route('master-equipment-type.show', ':id') }}".replace(':id', type_id), function (data) {
                $('#type-modal-heading').html("Edit Tipe Equipment");
                $('#save-type-btn').val("edit-type");
                $('#ajax-type-modal').modal('show');
                $('#type_id').val(data.id);
                $('#type_name').val(data.name);
            })
        });

        // Save Equipment Type
        $('#save-type-btn').click(function (e) {
            e.preventDefault();
            $(this).html('Mengirim..');

            $.ajax({
                data: $('#type-form').serialize(),
                url: "{{ route('master-equipment-type.store') }}",
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $('#type-form').trigger("reset");
                    $('#ajax-type-modal').modal('hide');
                    typeTable.ajax.reload();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.success,
                        icon: 'success'
                    });
                    $('#save-type-btn').html('Simpan');
                    window.location.reload();
                },
                error: function (data) {
                    console.log('Error:', data);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan, silakan coba lagi.',
                        icon: 'error'
                    });
                    $('#save-type-btn').html('Simpan');
                }
            });
        });

        // Delete Equipment Type
        $('body').on('click', '.delete-type', function () {
            var type_id = $(this).data("id");
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('master-equipment-type.destroy', ':id') }}".replace(':id', type_id),
                        success: function (data) {
                            typeTable.ajax.reload();
                            Swal.fire(
                                'Dihapus!',
                                'Data telah berhasil dihapus.',
                                'success'
                            );
                            window.location.reload();
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Datatable for Master Equipment
        var equipmentTable = $('#master-equipment-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('master-equipment.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'serial_number', name: 'serial_number'},
                {data: 'room_name', name: 'room_name'},
                {data: 'equipment_type_name', name: 'equipment_type_name', orderable: false, searchable: false},
                // {data: 'created_at', name: 'created_at'},
                // {data: 'updated_at', name: 'updated_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Add New Equipment
        $('#create-new-equipment').click(function () {
            $('#save-equipment-btn').val("create-equipment");
            $('#equipment_id').val('');
            $('#equipment-form').trigger("reset");
            $('#equipment-modal-heading').html("Tambah Equipment Baru");
            $('#ajax-equipment-modal').modal('show');
        });

        // Edit Equipment
        $('body').on('click', '.edit-equipment', function () {
            var equipment_id = $(this).data('id');
            $.get("{{ route('master-equipment.show', ':id') }}".replace(':id', equipment_id), function (data) {
                $('#equipment-modal-heading').html("Edit Equipment");
                $('#save-equipment-btn').val("edit-equipment");
                $('#ajax-equipment-modal').modal('show');
                $('#equipment_id').val(data.id);
                $('#equipment_name').val(data.name);
                $('#serial_number').val(data.serial_number);
                $('#room_id').val(data.room_id);
                $('#equipment_type_id').val(data.equipment_type_id);
            })
        });

        // Save Equipment
        $('#save-equipment-btn').click(function (e) {
            e.preventDefault();
            $(this).html('Mengirim..');

            $.ajax({
                data: $('#equipment-form').serialize(),
                url: "{{ route('master-equipment.store') }}",
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $('#equipment-form').trigger("reset");
                    $('#ajax-equipment-modal').modal('hide');
                    equipmentTable.ajax.reload();
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.success,
                        icon: 'success'
                    });
                    $('#save-equipment-btn').html('Simpan');
                },
                error: function (data) {
                    console.log('Error:', data);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan, silakan coba lagi.',
                        icon: 'error'
                    });
                    $('#save-equipment-btn').html('Simpan');
                }
            });
        });

        // Delete Equipment
        $('body').on('click', '.delete-equipment', function () {
            var equipment_id = $(this).data("id");
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('master-equipment.destroy', ':id') }}".replace(':id', equipment_id),
                        success: function (data) {
                            equipmentTable.ajax.reload();
                            Swal.fire(
                                'Dihapus!',
                                'Data telah berhasil dihapus.',
                                'success'
                            );
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });

// Menambahkan event handler untuk tombol close modal
$('.modal .close').on('click', function() {
    $(this).closest('.modal').modal('hide');
});
</script>
@endsection
