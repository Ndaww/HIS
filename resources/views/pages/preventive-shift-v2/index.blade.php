@extends('layouts.app')
@section('title', 'Daftar Ronde PM Per-Shift')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Ronde PM</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Ronde PM</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Riwayat Ronde Pengecekan Per-Shift</h5>
                    <a href="{{ route('pm_rounds.create') }}" class="btn btn-warning btn-sm">
                        + Mulai Ronde Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="pmRoundsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Shift</th>
                                    <th>Teknisi</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Anomali</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data akan dimuat via AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Pastikan Anda memiliki library DataTables --}}
{{-- <script src=".../datatables.min.js"></script> --}}

<script>
    $(document).ready(function() {
        // Tampilkan SweetAlert dari Session (untuk notifikasi setelah redirect)
        @if (session('success_message'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success_title') ?? 'Berhasil!' }}',
                text: '{{ session('success_message') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif
        
        @if (session('error_message'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error_title') ?? 'Gagal!' }}',
                text: '{{ session('error_message') }}',
            });
        @endif

        // ------------------------------------------
        // INISIALISASI DATATABLES
        // ------------------------------------------
        $('#pmRoundsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('pm_rounds.data') }}', // Route yang akan kita buat di Controller
            order: [[4, 'desc']], // Urutkan berdasarkan waktu mulai terbaru
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'round_status', name: 'round_status', className: 'text-center' },
                { data: 'shift_name', name: 'shift_name', className: 'text-center' },
                { data: 'technician_name', name: 'technician.name' },
                { data: 'start_time', name: 'start_time' },
                { data: 'completion_time', name: 'completion_time' },
                { data: 'total_anomalies', name: 'total_anomalies', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
        
        // ... (Logika Delete Ronde jika Anda ingin menambahkan, tapi biasanya Ronde tidak dihapus) ...

    });
</script>
@endsection