@extends('layouts.app') 

@section('title', 'Riwayat Pelaksanaan PM')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Riwayat Preventive Maintenance</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive V2</span> / Riwayat PM</div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 ">
            <h6 class="m-0 font-weight-bold">Daftar PM yang Telah Dilaksanakan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pmHistoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Form</th>
                            <th>Equipment</th>
                            <th>Teknisi</th>
                            <th>Tanggal PM</th>
                            <th>Waktu (Mulai - Selesai)</th>
                            <th>Durasi</th>
                            <th>Hasil Akhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables akan mengisi ini --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // ASUMSI: DataTable dan jQuery sudah dimuat di layout utama
        $('#pmHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            // PASTIKAN Anda telah mendefinisikan rute ini di web.php
            ajax: "{{ route('pm.get_history_data') }}", 
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'equipment_name', name: 'equipment.name' }, // Sesuaikan dengan nama kolom relasi
                { data: 'technician_name', name: 'technician.name' }, // Sesuaikan dengan nama kolom relasi
                { data: 'pm_date', name: 'pm_date' },
                { data: 'time_range', name: 'time_range', orderable: false, searchable: false },
                { data: 'duration', name: 'duration', orderable: false, searchable: false },
                { data: 'overall_result', name: 'overall_result' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endsection
