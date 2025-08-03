@extends('layouts.app')
@section('title','PKS - Pengjuan PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Pengjuan PKS</h2>
        <div class="breadcrumb" id="breadcrumb"><span>PKS</span> / Pengjuan PKS</div>
    </div>

    <div class="card">
        <div class="card-header">Pengjuan PKS</div>
        <div class="card-body">
            <form id="pks-form" action="{{ route('pks.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="partner_name" class="form-label">Nama Mitra</label>
                    <input type="text" name="partner_name" id="partner_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="cooperation_type" class="form-label">Jenis Kerja Sama</label>
                    <input type="text" name="cooperation_type" id="cooperation_type" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="objective" class="form-label">Tujuan</label>
                    <textarea name="objective" id="objective" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="initial_document" class="form-label">Dokumen Awal (PDF)</label>
                    <input type="file" name="initial_document" id="initial_document" class="form-control" accept=".pdf" required>
                </div>
                <button type="submit" class="btn btn-primary">Ajukan PKS</button>
            </form>

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
        $('#pks-form').on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `<p>Pengajuan PKS berhasil disubmit</p>`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        $('#pks-form')[0].reset();
                        $('#pks-form').find('input[type="file"]').val('');
                        $('#pks-form').find('textarea').val('');
                        $('#pks-form').find('select').val(null).trigger('change'); // Jika kamu pakai select2
                        $('#file-wrapper').empty();
                    });
                },
                error: function (xhr) {
                    let errorMsg = "Terjadi kesalahan saat mengirim.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMsg
                    });
                }
            });
        });
    });
</script>
@endsection
