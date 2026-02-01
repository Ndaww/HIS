@extends('layouts.app')
@section('title','Pencatatan Meter PLN')

@section('main-content')

    <div class="header-breadcrumb">
        <h2 id="page-title">Pencatatan Meter PLN</h2>
        <div class="breadcrumb" id="breadcrumb">
            <span>PLN Meter </span>
        </div>
    </div>

<section class="section dashboard">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Data Pencatatan</h5>
            <a href="pln-meter/create" class="btn btn-primary">+ Tambah Catatan</a>
            
        </div>

        <div class="card-body">
            <table class="table table-bordered" id="tbl-pln-meter" width="100%">
                <thead class="text-center">
                    <tr>
                        <th>No</th>
                        <th>ID Pelanggan PLN</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>COS PHI</th>
                        <th>WBP</th>
                        <th>LWBP</th>
                        <th>KWH</th>
                        <th>KVARH</th>
                        <th>Temuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</section>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="formEdit">
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pencatatan PLN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_id">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label>ID Pelanggan</label>
                            <input type="text" class="form-control" id="id_pelanggan_pln">
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" id="tanggal_pencatatan">
                        </div>
                        <div class="col-md-6">
                            <label>Jam</label>
                            <input type="time" class="form-control" id="jam_pencatatan">
                        </div>
                        <div class="col-md-4">
                            <label>COS PHI</label>
                            <input type="number" step="0.001" class="form-control" id="cos_phi">
                        </div>
                        <div class="col-md-4">
                            <label>WBP</label>
                            <input type="number" class="form-control" id="wbp">
                        </div>
                        <div class="col-md-4">
                            <label>LWBP</label>
                            <input type="number" class="form-control" id="lwbp">
                        </div>
                        <div class="col-md-6">
                            <label>KWH</label>
                            <input type="number" class="form-control" id="kwh">
                        </div>
                        <div class="col-md-6">
                            <label>KVARH</label>
                            <input type="number" class="form-control" id="kvarh">
                        </div>
                        <div class="col-12">
                            <label>Temuan</label>
                            <textarea class="form-control" id="temuan"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('js')
<script>
$(function () {
    $('#tbl-pln-meter').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('pln-meter.data') }}",
        lengthMenu: [10, 25, 50, 100, -1],
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
        searching: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: "text-center" ,orderable:false, searchable:false },
            { data: 'id_pelanggan_pln', name: 'id_pelanggan_pln' },
            { data: 'tanggal_pencatatan', name: 'tanggal_pencatatan', className: 'text-center' },
            { data: 'jam_pencatatan', name: 'jam_pencatatan', className: 'text-center' },
            { data: 'cos_phi', name: 'cos_phi', className: 'text-center' },
            { data: 'wbp', name: 'wbp' },
            { data: 'lwbp', name: 'lwbp' },
            { data: 'kwh', name: 'kwh' },
            { data: 'kvarh', name: 'kvarh' },
            { data: 'temuan', name: 'temuan' },
            { data: 'aksi', name: 'aksi', orderable:false, searchable:false, className:'text-center' },
        ]
    });
});

function editData(id) {
    $.get("{{ url('pln-meter') }}/" + id, function (data) {
        $('#edit_id').val(data.id);
        $('#id_pelanggan_pln').val(data.id_pelanggan_pln);
        $('#tanggal_pencatatan').val(data.tanggal_pencatatan);
        $('#jam_pencatatan').val(data.jam_pencatatan);
        $('#cos_phi').val(data.cos_phi);
        $('#wbp').val(data.wbp);
        $('#lwbp').val(data.lwbp);
        $('#kwh').val(data.kwh);
        $('#kvarh').val(data.kvarh);
        $('#temuan').val(data.temuan);

        $('#modalEdit').modal('show');
    });
}

$('#formEdit').submit(function (e) {
    e.preventDefault();

    let id = $('#edit_id').val();

    $.ajax({
        url: "{{ url('pln-meter') }}/" + id,
        type: "POST",
        data: {
            _method: "PUT",
            _token: "{{ csrf_token() }}",
            id_pelanggan_pln: $('#id_pelanggan_pln').val(),
            jam_pencatatan: $('#jam_pencatatan').val(),
            cos_phi: $('#cos_phi').val(),
            wbp: $('#wbp').val(),
            lwbp: $('#lwbp').val(),
            kwh: $('#kwh').val(),
            kvarh: $('#kvarh').val(),
            temuan: $('#temuan').val(),
        },
        beforeSend: function () {
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        },
        success: function (res) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            });

            $('#modalEdit').modal('hide');
            $('#tbl-pln-meter').DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
            let msg = 'Terjadi kesalahan';

            if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg
            });
        }
    });
});

</script>
@endsection
