@extends('layouts.app')
@section('title','PKS - Approval Direksi')

@section('main-content')
<div class="header-breadcrumb">
    <h2 id="page-title">Approval Direksi</h2>
        <div class="breadcrumb" id="breadcrumb"><span class="me-1">PKS </span> / Approval Direksi</div>
</div>

<div class="card">
    <div class="card-header">Pengajuan PKS Saya</div>
    <div class="card-body">
        <form id="filter-form" class="row g-3 mb-4">
            <div class="col-md-5">
                <label>Tanggal Awal</label>
                <input type="date" id="start_date" class="form-control" name="start_date">
            </div>
            <div class="col-md-5">
                <label>Tanggal Akhir</label>
                <input type="date" id="end_date" class="form-control" name="end_date">
            </div>
            <div class="col-md-2 align-self-end mt-3">
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
                    <th>Tanggal Awal</th>
                    <th>Tanggal Akhir</th>
                    <th>Draft</th>
                    <th>Tanggal Buat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal reject -->
<div class="modal fade" id="modal-reject-approval" tabindex="-1">
    <div class="modal-dialog">
        <form id="reject-approval-form" action="{{ route('pks.rejectApproval') }}" method="POST">
            @csrf
            <input type="hidden" name="pks_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tolak PKS</h5></div>
                <div class="modal-body">
                    <textarea name="note" class="form-control" placeholder="Alasan penolakan" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    const table = $('#pks-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        // ajax: '{{ route("pks.approval") }}',
        ajax: {
            url: '{{ route("pks.approval") }}',
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
                title: 'Daftar Ticketing'
            },
            {
                extend: 'print',
                className: 'btn btn-primary',
                title: 'Daftar Ticketing'
            }
        ],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'partner_name', name: 'partner_name' },
            { data: 'cooperation_type', name: 'cooperation_type' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
            // {
            //     data: null,
            //     render: data => `${data.start_date} s/d ${data.end_date}`
            // },
            { data: 'draft', name: 'draft' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
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

    function approve(id) {
        Swal.fire({
            title: 'Yakin ingin menyetujui PKS ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui'
        }).then(result => {
            if (result.isConfirmed) {
                $.post("{{ route('pks.approve') }}", { _token: '{{ csrf_token() }}', pks_id: id }, function (res) {
                    Swal.fire('Disetujui!', res.message, 'success');
                    table.ajax.reload();
                }).fail(function (xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                });
            }
        });
    }

    $('#modal-reject-approval').on('show.bs.modal', function (e) {
        const id = $(e.relatedTarget).data('id');
        $('#reject-approval-form input[name="pks_id"]').val(id);
    });

    $('#reject-approval-form').on('submit', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function (res) {
            $('#modal-reject-approval').modal('hide');
            Swal.fire('Ditolak!', res.message, 'success');
            table.ajax.reload();
        }).fail(function (xhr) {
            Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
        });
    });
</script>
@endsection
