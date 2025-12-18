@extends('layouts.app')
@section('title','Daftar Facility Tour')
@section('main-content')

<div class="header-breadcrumb">
        <h2 id="page-title">Daftar Facility Tour</h2>
        <div class="breadcrumb" id="breadcrumb">
            <span>Facility</span> / Daftar Facility Tour
        </div>
    </div>

<div class="card mt-3">
    <div class="card-header">Daftar Facility Tour</div>
    <div class="card-body">
        <table class="table table-bordered" id="facility-tour-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelapor</th>
                    <th>Judul</th>
                    <th>Ruangan</th>
                    <th>Risk</th>
                    <th>Departemen</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@section('js')
<script>
$(function() {
    $('#facility-tour-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('facility-tour.index') }}",
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
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'pelapor', name: 'pelapor', className: 'text-capitalize' },
            { data: 'title', name: 'title', className: 'text-capitalize' },
            { data: 'room_name', name: 'master_rooms.name', defaultContent: '-' },
            { data: 'risk_grading', name: 'risk_grading', className: 'text-capitalize' },
            { data: 'department_name', name: 'departments.name', defaultContent: '-', className: 'text-capitalize' },
            { data: 'created_at', name: 'created_at', className: 'text-capitalize' },
        ],
        language: {
            emptyTable: "Belum ada data facility tour"
        }
    });
});
</script>
@endsection
