@extends('layouts.app')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Tiket</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Ticketing</span> / Daftar Tiket </div>
        {{-- <div class="breadcrumb" id="breadcrumb">Beranda</div> --}}
    </div>

    <div class="card">
        <div class="card-header">Daftar Tiket</div>
        <div class="card-body">
            <table id="tickets-table" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Tiket</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Assigned</th>
                    <th>Status</th>
                    <th>Prioritas</th>
                    <th>Dibuat</th>
                    <th>Update</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
        </div>
        
    </div>

    

<script src="{{asset('/assets/js/jquery.js')}}"></script>
<script src="{{asset('/assets/js/jquery.datatables.js')}}"></script>
<script>
  $(document).ready(function() {
        $('#tickets-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '{{ route('list-ticket') }}',
            dom: 'lBfrtip', 
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Laporan Pengunjung'
                },
                {
                    extend: 'print',
                    title: 'Laporan Pengunjung'
                }
            ],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex',orderable: false, searchable: false },
                { data: 'ticket_number' },
                { data: 'title' },
                { data: 'description' },
                { data: 'requester_id' },
                { data: 'department_id' },
                { data: 'assigned_employee_id' },
                { data: 'status' },
                { data: 'priority' },
                { data: 'created_at' },
                { data: 'updated_at' },
                { data: 'action', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endsection