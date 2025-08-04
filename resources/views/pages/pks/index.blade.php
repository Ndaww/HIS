@extends('layouts.app')
@section('title','PKS - Verifikasi PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Verifikasi PKS</h2>
        <div class="breadcrumb" id="breadcrumb"><span>PKS</span> / Verifikasi PKS</div>
    </div>

    <div class="card">
        <div class="card-header">Verifikasi PKS</div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="filter-form" class="row g-3 mb-4">
                <div class="col-md-5">
                    <label>Tanggal Awal</label>
                    <input type="date" id="start_date" class="form-control" name="start_date">
                </div>
                <div class="col-md-5">
                    <label>Tanggal Akhir</label>
                    <input type="date" id="end_date" class="form-control" name="end_date">
                </div>
                <div class="col-2 align-self-end mt-3">
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
                        <th>Note</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>

        </div>
        </div>
    </div>

    {{-- Modal verify --}}
    <div class="modal fade" id="modal-verify" tabindex="-1">
        <div class="modal-dialog">
            <form id="verify-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="pks_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Upload Draft PKS</h5></div>
                <div class="modal-body">
                <input type="file" name="draft_document" class="form-control" required accept=".pdf">
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-success"> <i class="ri ri-check-line"></i> Verifikasi</button>
                </div>
            </div>
            </form>
        </div>
    </div>

    {{-- Modal Reject --}}
    <div class="modal fade" id="modal-reject" tabindex="-1">
        <div class="modal-dialog">
            <form id="reject-form" action="{{ route('pks.reject') }}" method="POST">
            @csrf
            <input type="hidden" name="pks_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tolak Pengajuan</h5></div>
                <div class="modal-body">
                <textarea name="note" class="form-control" required placeholder="Masukkan alasan penolakan"></textarea>
                </div>
                <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    {{-- Modal Upload Ulang Draft --}}
    <div class="modal fade" id="modal-reupload-draft" tabindex="-1">
        <div class="modal-dialog">
            <form id="reupload-draft-form" action="{{ route('pks.reupload.draft') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pks_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Ulang Draft PKS</h5>
                    </div>
                    <div class="modal-body">
                        <input type="file" name="draft_document" class="form-control" accept=".pdf" required>
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

    {{-- Modal Final --}}
    <div class="modal fade" id="modal-upload-final" tabindex="-1">
        <div class="modal-dialog">
            <form id="upload-final-form" action="{{ route('pks.uploadFinal') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pks_id">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Upload Dokumen Final</h5></div>
                    <div class="modal-body">
                        <input type="file" name="final_document" class="form-control" accept=".pdf" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"><i class="ri ri-upload-2-line"></i> Upload</button>
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
                url: '{{ route("pks.verify") }}',
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

        // Modal upload draft (verifikasi)
        $('#modal-verify').on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const id = button.data('id');
            $('#verify-form input[name="pks_id"]').val(id);
        });

        // Modal reject
        $('#modal-reject').on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const id = button.data('id');
            $('#reject-form input[name="pks_id"]').val(id);
        });

        // Modal reupload draft
        $('#modal-reupload-draft').on('show.bs.modal', function (e) {
            const id = $(e.relatedTarget).data('id');
            $('#reupload-draft-form input[name="pks_id"]').val(id);
        });

        // Modal final
        $('#modal-upload-final').on('show.bs.modal', function (e) {
            const id = $(e.relatedTarget).data('id');
            $('#upload-final-form input[name="pks_id"]').val(id);
        });

        // Submit Verifikasi
        $('#verify-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#modal-verify').modal('hide');
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#pks-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });

        // Submit Penolakan
        $('#reject-form').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#modal-reject').modal('hide');
                    Swal.fire('Ditolak!', res.message, 'success');
                    $('#pks-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });

        // Submit Upload Ulang Draft
        $('#reupload-draft-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modal-reupload-draft').modal('hide');
                    Swal.fire('Berhasil!', res.message, 'success');
                    $('#pks-table').DataTable().ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                }
            });
        });

        // Submit upload final
        $('#upload-final-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modal-upload-final').modal('hide');
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
