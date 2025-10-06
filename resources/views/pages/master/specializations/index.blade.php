@extends('layouts.app')
@section('title', 'Master Spesialisasi')
@section('main-content')
<div class="header-breadcrumb">
    <h2 id="page-title">Daftar Spesialisasi</h2>
    <div class="breadcrumb" id="breadcrumb"> <span>Spesialisasi </span> / Daftar Spesialisasi </div>
</div>

<div class="card">
    <div class="card-header">Daftar Spesialisasi</div>
    <div class="card-body">
        <div class="mb-3">
            <button class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="ri ri-add-large-fill"></i> Tambah Spesialisasi
            </button>

        </div>
        <table id="specializations-table" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Spesialisasi</th>
                    <th>Deskripsi</th>
                    <th>Tipe Equipment</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="create-form" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Spesialisasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-12">
              <label>Nama Spesialisasi</label>
              <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-12">
              <label>Deskripsi</label>
              <textarea name="description" class="form-control"></textarea>
          </div>
          <div class="col-12">
              <label>Tipe Equipment</label>
              <select name="type_id" id="type_id" class="form-select" required>
                    <option value="">Pilih Tipe Equipment</option>
                    @foreach ($equipments as $row)
                        <option value="{{$row->id}}">{{$row->name}}</option>
                    @endforeach
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

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="edit-form" method="POST">
      @csrf
      {{-- @method('PUT') --}}
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Spesialisasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" id="edit-id" name="id">
          <div class="col-md-12">
              <label>Nama Spesialisasi</label>
              <input type="text" name="name" class="form-control" id="edit-name">
          </div>
          <div class="col-md-12">
              <label>Deskripsi</label>
              <textarea name="description" class="form-control" id="edit-description"></textarea>
          </div>
          <div class="col-12">
              <label>Tipe Equipment</label>
              <select name="type_id" id="edit-type" class="form-select" required>
                    @foreach ($equipments as $row)
                        <option value="{{$row->id}}">{{$row->name}}</option>
                    @endforeach
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
    const table = $('#specializations-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('specializations.data') }}",
        lengthMenu: [10, 25, 50, 100],
        dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
            "<'row mb-3'<'col-sm-12'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'btn btn-success',
                title: 'Daftar Spesialisasi'
            },
            {
                extend: 'print',
                className: 'btn btn-primary',
                title: 'Daftar Spesialisasi'
            }
        ],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'description' },
            { data: 'equipment_name', name: 'equipment_name' }, 
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // buka modal edit
    $('#specializations-table').on('click', '.btn-edit', function () {
        const data = $(this).data('json');

        $('#edit-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-description').val(data.description);
        $('#edit-type').val(data.type_id); 

        $('#editModal').modal('show');
    });

    // submit update
    $('#edit-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = `/master/specializations/${id}`;
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

    // submit create
    $('#create-form').on('submit', function (e) {
        e.preventDefault();
        const url = "{{ route('specializations.store') }}";
        const data = $(this).serialize();

        $.ajax({
            url,
            method: 'POST',
            data,
            success: function (res) {
                Swal.fire('Berhasil', res.message, 'success');
                $('#createModal').modal('hide');
                $('#create-form')[0].reset();
                table.ajax.reload();
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan';
                if (xhr.status === 422) {
                    message = Object.values(xhr.responseJSON.errors)
                        .map(v => `<li>${v[0]}</li>`).join('');
                    message = `<ul>${message}</ul>`;
                }
                Swal.fire('Gagal', message, 'error');
            }
        });
    });

    // tombol hapus
$('#specializations-table').on('click', '.btn-delete', function () {
    const id = $(this).data('id');

    Swal.fire({
        title: 'Yakin hapus data?',
        text: "Data yang sudah dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/master/specializations/${id}`,
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#specializations-table').DataTable().ajax.reload();
                },
                error: function (xhr) {
                    let message = 'Terjadi kesalahan';
                    if (xhr.status === 422) {
                        message = Object.values(xhr.responseJSON.errors)
                            .map(v => `<li>${v[0]}</li>`).join('');
                        message = `<ul>${message}</ul>`;
                    }
                    Swal.fire('Gagal', message, 'error');
                }
            });
        }
    });
});
});
</script>
@endsection
