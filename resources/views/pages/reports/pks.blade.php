@extends('layouts.app')
@section('title','PKS - Pengajuan PKS Saya')
@section('main-content')
<span class="badge badge-secondary"></span>
    <div class="header-breadcrumb">
        <h2 id="page-title">Pengajuan PKS Saya</h2>
        <div class="breadcrumb" id="breadcrumb"><span>PKS</span> / Pengajuan PKS Saya</div>
    </div>

    <div class="card">
        <div class="card-header">Pengajuan PKS Saya</div>
        <div class="card-body">
            <form id="filter-form" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label>Tanggal Awal</label>
                    <input type="date" id="start_date" class="form-control" name="start_date">
                </div>
                <div class="col-md-3">
                    <label>Tanggal Akhir</label>
                    <input type="date" id="end_date" class="form-control" name="end_date">
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select class="form-control" name="status" id="status">
                        <option value="">-- Pilih Status --</option>
                        <option value="submitted">Submitted</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                        <option value="approved">Approved</option>
                        <option value="signed">Signed</option>
                    </select>
                </div>
                <div class="col-md-3 align-self-end mt-3">
                    <button type="button" id="filterBtn" class="btn btn-primary">
                        <i class="ri ri-filter-line"></i> Filter
                    </button>
                    <button type="button" id="resetBtn" class="btn btn-secondary">
                        <i class="ri ri-refresh-line"></i> Reset
                    </button>
                </div>
            </form>
            <table id="pks-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Mitra</th>
                        <th>Jenis Kerja Sama</th>
                        <th>Tujuan</th>
                        <th>Tanggal Awal</th>
                        <th>Tanggal Akhir</th>
                        <th>Dokumen Awal</th>
                        <th>Draft</th>
                        <th>Final</th>
                        <th>Tanggal Buat</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>

        </div>
        </div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    </div>

@endsection

@section('js')
<script>
    $(document).ready(function () {
        const table = $('#pks-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("list-report-pks") }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.status = $('#status').val();
                }
            },

            lengthMenu: [10, 25, 50, 100, -1],
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
                "<'row mb-3'<'col-sm-12'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success',
                    title: 'Laporan Perjanjian Kerjasama'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: 'Laporan Perjanjian Kerjasama'
                }
            ],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'partner_name', name: 'partner_name' },
                { data: 'cooperation_type', name: 'cooperation_type' },
                { data: 'objective', name: 'objective' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                // { data: 'duration', name: 'duration', orderable: false, searchable: false },
                { data: 'initial_document', name: 'initial_document', className:'text-center', orderable: false, searchable: false },
                { data: 'draft_document', name: 'draft_document', className:'text-center', orderable: false, searchable: false },
                { data: 'final_document', name: 'final_document', className:'text-center', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'status', name: 'status', className:'text-center' },
            ]
        });

        // Tombol Filter
        $('#filterBtn').click(function () {
            table.ajax.reload();
        });

        // Tombol Reset
        $('#resetBtn').click(function () {
            $('#filter-form')[0].reset();
            table.ajax.reload();
        });
    });

</script>
@endsection