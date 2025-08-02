@extends('layouts.app')
@section('title','Preventive - History Tugas Preventive')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">History Tugas Preventive</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / History Tugas Preventive</div>
    </div>

    <div class="card">
        <div class="card-header">
            History Tugas Preventive
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3 mb-4">
                <div class="col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="end_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Lantai</label>
                    <select id="floor" class="form-control">
                        <option value="">Semua</option>
                        <option value="Lantai 1">Lantai 1</option>
                        <option value="Lantai 2">Lantai 2</option>
                        <option value="Lantai 3">Lantai 3</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Ruangan</label>
                    <select id="room_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->floor }} - {{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Jenis Alat</label>
                    <select id="equipment_type_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach($equipmentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Status</label>
                    <select id="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="done">Selesai</option>
                        <option value="in_progress">Dalam Proses</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <button type="button" id="filterBtn" class="btn btn-primary">
                        <i class="ri ri-filter-line"></i> Filter
                    </button>
                    <button type="button" id="resetBtn" class="btn btn-secondary">
                        <i class="ri ri-refresh-line"></i> Reset
                    </button>
                </div>

            </form>

            <table class="table table-bordered" id="report-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Lantai & Ruangan</th>
                        <th>Nama Alat</th>
                        <th>Tipe</th>
                        <th>Teknisi</th>
                        <th>Tindakan</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>

</div>


@endsection

@section('js')
<script>
    $(document).ready(function () {
        let table = $('#report-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('list-report-preventive') }}",
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.floor = $('#floor').val();
                    d.room_id = $('#room_id').val();
                    d.equipment_type_id = $('#equipment_type_id').val();
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
                    title: 'Daftar Ticketing'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: 'Daftar Ticketing'
                }
            ],
            columns: [
                { data: 'tanggal', name: 'performed_date' },
                { data: 'ruangan', name: 'room.name' },
                { data: 'alat', name: 'equipment.name' },
                { data: 'equipment.type.name', name: 'equipment_type' },
                { data: 'teknisi', name: 'executor.name' },
                { data: 'tindakan', name: 'details.preventiveType.name' },
                { data: 'status', name: 'status' },
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

        // onchange
        // $('#filter-form select, #filter-form input').on('change', function () {
        //     table.ajax.reload();
        // });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil'
            text: '{{ session('success') }}'
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}'
        });
    @endif
</script>
@endsection
