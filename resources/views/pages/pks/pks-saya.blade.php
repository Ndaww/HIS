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
                        <th>Tanggal Buat</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
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

    {{-- Modal Upload Ulang Dokumen --}}
    <div class="modal fade" id="modal-resubmit" tabindex="-1">
        <div class="modal-dialog">
            <form id="resubmit-form" action="{{ route('pks.resubmit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pks_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Ulang Dokumen PKS</h5>
                    </div>
                    <div class="modal-body">
                        <label>Dokumen Awal (PDF)</label>
                        <input type="file" name="initial_document" class="form-control" required accept=".pdf">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="ri ri-upload-2-line"></i> Upload Ulang
                        </button>
                    </div>
                </div>
            </form>
        </div>
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
                url: '{{ route("pks.pengajuan-saya") }}',
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
                { data: 'objective', name: 'objective' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                // { data: 'duration', name: 'duration', orderable: false, searchable: false },
                {
                    data: 'initial_document',
                    name: 'initial_document',
                    render: function(data) {
                        return `<a href="/storage/${data}" target="_blank">Lihat Dokumen</a>`;
                    }
                },
                { data: 'created_at', name: 'created_at' },
                { data: 'status', name: 'status' },
                { data: 'note', name: 'note' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
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

        // Buka modal dan set pks_id
        $('#modal-resubmit').on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const id = button.data('id');
            $('#resubmit-form input[name="pks_id"]').val(id);
        });

        // Submit Upload Ulang
        $('#resubmit-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modal-resubmit').modal('hide');
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#pks-table').DataTable().ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });
    });

</script>
@endsection
