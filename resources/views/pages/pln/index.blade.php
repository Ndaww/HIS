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
</script>
@endsection
