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
            <h4 class="mb-3">History Tugas Preventive</h4>

            <form id="filter-form" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="start_date">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="button" id="btn-filter" class="btn btn-primary"> <i class="ri ri-filter-line"></i> Filter </button>
                    <button type="button" id="btn-reset" class="btn btn-secondary"> <i class="ri ri-refresh-line"></i> Reset</button>
                </div>
            </form>

            <table class="table table-bordered" id="history-table">
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Alat</th>
                        <th>Tindakan</th>
                        <th>Tanggal</th>
                        <th>Teknisi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>


@endsection

@section('js')
<script>
    $(function () {
        let table = $('#history-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('preventive-task.history.data') }}",
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
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
                    title: 'History Tugas Saya'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: 'History Tugas Saya'
                }
            ],
            columns: [
                { data: 'ruangan', name: 'ruangan' },
                { data: 'alat', name: 'alat' },
                { data: 'tindakan', name: 'tindakan' },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'teknisi', name: 'teknisi' },
            ]
        });

        $('#btn-filter').click(() => table.ajax.reload());
        $('#btn-reset').click(() => {
            $('#start_date').val('');
            $('#end_date').val('');
            table.ajax.reload();
        });
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
