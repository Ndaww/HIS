@extends('layouts.app')
@section('title', 'Master Ruangan')
@section('main-content')
    <div class="header-breadcrumb">
    <h2 id="page-title">Daftar Ruangan</h2>
    <div class="breadcrumb" id="breadcrumb"> <span>Ruangan </span> / Daftar Ruangan </div>
</div>

<div class="card">
    <div class="card-header">Daftar Ruangan</div>
    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-primary text-white" href="/master/rooms/create"><i class="ri ri-add-large-fill"></i> Tambah Ruangan </a>
        </div>
        <table id="rooms-table" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ruangan</th>
                    <th>Lantai</th>
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="edit-form" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Ruangan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" id="edit-id">
          <div class="col-md-6">
              <label>Nama Ruangan</label>
              <input type="text" name="name" class="form-control" id="edit-name">
          </div>
          <div class="col-md-3">
              <label>Lantai</label>
              <select name="floor" id="edit-floor" class="form-control">
                  <option value="Lantai 1">Lantai 1</option>
                  <option value="Lantai 2">Lantai 2</option>
                  <option value="Lantai 3">Lantai 3</option>
                  <option value="Lantai 4">Lantai 4</option>
                  <option value="Lantai 5">Lantai 5</option>
              </select>
          </div>
          <div class="col-md-3">
              <label>Kelas</label>
              <select name="class" id="edit-class" class="form-control">
                  <option value="KELAS 1">Kelas 1</option>
                  <option value="KELAS 2">Kelas 2</option>
                  <option value="KELAS 3">Kelas 3</option>
                  <option value="VIP">VIP</option>
                  <option value="VVIP">VVIP</option>
              </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    const table = $('#rooms-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('rooms.data') }}",
        lengthMenu: [10, 25, 50, 100],
        dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
            "<'row mb-3'<'col-sm-12'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'btn btn-success',
                title: 'Daftar Ticketing'
            },
            {
                extend: 'print',
                className: 'btn btn-primary',
                title: 'Daftar Ticketing'
            }
        ],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'floor', name: 'floor' },
            { data: 'class', name: 'class' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // buka modal edit
    $('#rooms-table').on('click', '.btn-edit', function () {
        const data = $(this).data('json');

        $('#edit-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-floor').val(data.floor);
        $('#edit-class').val(data.class);

        $('#editModal').modal('show');
    });

    // submit update
    $('#edit-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = `/master/rooms/${id}`;
        const data = $(this).serialize();

        $.ajax({
            url,
            method: 'POST',
            data,
            success: function (res) {
                Swal.fire('Berhasil', res.message, 'success');
                $('#editModal').modal('hide');
                table.ajax.reload();
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan';
                if (xhr.status === 422) {
                    message = Object.values(xhr.responseJSON.errors).map(v => `<li>${v[0]}</li>`).join('');
                    message = `<ul>${message}</ul>`;
                }
                Swal.fire('Gagal', message, 'error');
            }
        });
    });
});
</script>
@endsection