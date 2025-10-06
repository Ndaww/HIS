@extends('layouts.app')
@section('title', 'Master Teknisi')
@section('main-content')
<div class="header-breadcrumb">
    <h2 id="page-title">Daftar Teknisi</h2>
    <div class="breadcrumb"> <span>Teknisi </span> / Daftar Teknisi </div>
</div>

<div class="card">
    <div class="card-header">Daftar Teknisi</div>
    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-primary text-white" href="/master/technicians/create">
                <i class="ri ri-add-large-fill"></i> Tambah Teknisi
            </a>
        </div>
        <table id="technicians-table" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Spesialisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal Assign Spesialis --}}
<div class="modal fade" id="assignSpecialistModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="assign-specialist-form" method="POST">
      @csrf
      <input type="hidden" id="assign-user-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Spesialis untuk <span id="assign-user-name"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-12">
              <label>Pilih Spesialis</label>
              <select name="specialization_id" id="assign-specialization" class="form-control">
                  @foreach(\App\Models\Specializations::all() as $spec)
                      <option value="{{ $spec->id }}">{{ $spec->name }}</option>
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
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Technician</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" id="edit-id">
          <div class="col-md-12">
              <label>Nama</label>
              <input type="text" name="name" class="form-control" id="edit-name">
          </div>
          <div class="col-md-12">
              <label>Email</label>
              <input type="email" name="email" class="form-control" id="edit-email">
          </div>
          <div class="col-md-12">
              <label>Password (kosongkan jika tidak diganti)</label>
              <input type="password" name="password" class="form-control" id="edit-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Modal Hapus Spesialis --}}
<div class="modal fade" id="removeSpecialistModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Hapus Spesialisasi dari <span id="remove-user-name"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="user-specializations-list">
          {{-- Spesialisasi akan di-load via AJAX --}}
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function () {
    const table = $('#technicians-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('technicians.data') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'email' },
            { data: 'spesialisasi' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // buka modal edit
    $('#technicians-table').on('click', '.btn-edit', function () {
        const data = $(this).data('json');
        $('#edit-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-email').val(data.email);
        $('#editModal').modal('show');
    });

    // submit update
    $('#edit-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = `/master/technicians/${id}`;
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
                Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
            }
        });
    });

    // hapus data
    $('#technicians-table').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Yakin hapus?',
            icon: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/master/technicians/${id}`,
                    method: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function (res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        table.ajax.reload();
                    }
                });
            }
        });
    });

    // buka modal assign
    $('#technicians-table').on('click', '.btn-add-specialist', function () {
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        $('#assign-user-id').val(userId);
        $('#assign-user-name').text(userName);
        $('#assignSpecialistModal').modal('show');
    });

    // submit assign
    $('#assign-specialist-form').on('submit', function (e) {
        e.preventDefault();
        const userId = $('#assign-user-id').val();
        const specializationId = $('#assign-specialization').val();

        $.ajax({
            url: `/master/technicians/${userId}/assign-specialist`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                specialization_id: specializationId
            },
            success: function (res) {
                Swal.fire('Berhasil', res.message, 'success');
                $('#assignSpecialistModal').modal('hide');
                table.ajax.reload();
            },
            error: function () {
                Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
            }
        });
    });

    // buka modal hapus spesialis
    $('#technicians-table').on('click', '.btn-remove-specialist', function () {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#remove-user-name').text(userName);

        // load spesialisasi user via AJAX
        $.ajax({
            url: `/master/technicians/${userId}/specializations`,
            method: 'GET',
            success: function(res) {
                const list = $('#user-specializations-list');
                list.empty();
                if(res.length === 0) {
                    list.append('<li class="list-group-item">Belum ada spesialisasi</li>');
                } else {
                    res.forEach(function(spec){
                        const item = $(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                ${spec.name}
                                <button class="btn btn-danger btn-sm btn-delete-spec" data-user-id="${userId}" data-spec-id="${spec.id}">
                                    Hapus
                                </button>
                            </li>
                        `);
                        list.append(item);
                    });
                }
                $('#removeSpecialistModal').modal('show');
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak bisa memuat spesialisasi', 'error');
            }
        });
    });

    // hapus spesialisasi
    $('#user-specializations-list').on('click', '.btn-delete-spec', function() {
        const userId = $(this).data('user-id');
        const specId = $(this).data('spec-id');
        const btn = $(this);

        Swal.fire({
            title: 'Yakin hapus spesialisasi ini?',
            icon: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: `/master/technicians/${userId}/remove-specialist`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        specialization_id: specId
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        btn.closest('li').remove();
                        table.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });


});
</script>
@endsection
