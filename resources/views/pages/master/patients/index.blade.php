@extends('layouts.app')
@section('title', 'Registrasi Pasien')
@section('main-content')
    <div class="header-breadcrumb">
    <h2 id="page-title">Daftar Pasien</h2>
    <div class="breadcrumb" id="breadcrumb"> <span>Pasien</span> / Daftar Pasien </div>
</div>

<div class="card">
    <div class="card-header">Daftar Pasien</div>
    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-primary text-white" href="/master/patients/create"><i class="ri ri-add-large-fill"></i> Registrasi Pasien </a>
        </div>
        <table id="patients-table" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jenis Kelamin</th>
                    <th>Tanggal Lahir</th>
                    <th>No KTP</th>
                    <th>No BPJS</th>
                    <th>Alamat</th>
                    <th>No Telepon</th>
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
          <h5 class="modal-title">Edit Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
          <input type="hidden" id="edit-id">
          <div class="col-md-6">
              <label>Nama</label>
              <input type="text" name="name" class="form-control" id="edit-name">
          </div>
          <div class="col-md-3">
              <label>Gender</label>
              <select name="gender" id="edit-gender" class="form-control">
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
              </select>
          </div>
          <div class="col-md-3">
              <label>Tgl Lahir</label>
              <input type="date" name="birth_date" class="form-control" id="edit-birth_date">
          </div>
          <div class="col-md-6">
              <label>No KTP</label>
              <input type="text" name="no_ktp" class="form-control" id="edit-no_ktp">
          </div>
          <div class="col-md-6">
              <label>No BPJS</label>
              <input type="text" name="no_bpjs" class="form-control" id="edit-no_bpjs">
          </div>
          <div class="col-md-6">
              <label>Alamat</label>
              <input type="text" name="address" class="form-control" id="edit-address">
          </div>
          <div class="col-md-6">
              <label>No Telepon</label>
              <input type="text" name="phone" class="form-control" id="edit-phone">
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
    const table = $('#patients-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('patients.data') }}",
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
            { data: 'gender_text', name: 'gender' },
            { data: 'birth_date' },
            { data: 'no_ktp' },
            { data: 'no_bpjs' },
            { data: 'address' },
            { data: 'phone' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // buka modal edit
    $('#patients-table').on('click', '.btn-edit', function () {
        const data = $(this).data('json');

        $('#edit-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-gender').val(data.gender);
        $('#edit-birth_date').val(data.birth_date);
        $('#edit-no_ktp').val(data.no_ktp);
        $('#edit-no_bpjs').val(data.no_bpjs);
        $('#edit-address').val(data.address);
        $('#edit-phone').val(data.phone);

        $('#editModal').modal('show');
    });

    // submit update
    $('#edit-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = `/master/patients/${id}`;
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