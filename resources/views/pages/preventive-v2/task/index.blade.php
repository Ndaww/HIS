@extends('layouts.app')

@section('title','Daftar Tugas Preventive Maintenance Saya')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Tugas Preventive Maintenance Saya</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Tugas Saya</div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Jadwal PM yang Belum Dieksekusi (Status: Scheduled)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                {{-- Pastikan ID tabel ini cocok dengan selector di JS --}}
                <table class="table table-bordered table-striped" id="myPmTasksTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th width="2%">No</th>
                            <th>Nama Equipment</th>
                            <th>Target Bulan/Tahun</th>
                            <th>Tanggal Dijadwalkan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data akan diisi oleh DataTables melalui AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
{{-- Pastikan Anda sudah memuat jQuery dan DataTables JS/CSS di layout induk (layouts.app) --}}
<script>
$(document).ready(function() {
    $('#myPmTasksTable').DataTable({ // Menggunakan ID yang berbeda untuk menghindari konflik
        processing: true,
        serverSide: true,
        // Pastikan rute ini sudah didefinisikan di routes/web.php
        ajax: "{{ route('pm.tasks.data') }}", 
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, 
            { data: 'name_equipment', name: 'name_equipment' }, 
            { data: 'target_period', name: 'target_period' },
            { data: 'scheduled_date', name: 'scheduled_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }, 
        ],
        // Opsi lain yang mungkin berguna:
        order: [[3, 'asc']] // Urutkan berdasarkan kolom Tanggal Dijadwalkan (kolom indeks 3)
    });
});
</script>
@endsection