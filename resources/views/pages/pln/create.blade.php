@extends('layouts.app')
@section('title','Pencatatan Meter PLN - Input')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Pencatatan Meter PLN</h2>
        <div class="breadcrumb" id="breadcrumb">
            <span>PLN Meter</span> / Input Harian
        </div>
    </div>

    <div class="card">
        <div class="card-header">Form Pencatatan Meter PLN</div>
        <div class="card-body">

            <form id="pln-form" action="{{ route('pln-meter.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">ID Pelanggan PLN</label>
                    <input type="text" name="id_pelanggan_pln" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Pencatatan</label>
                    <input type="date" name="tanggal_pencatatan" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jam Pencatatan</label>
                    <input type="time" name="jam_pencatatan" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cos Phi</label>
                    <input type="number" step="0.001" name="cos_phi" class="form-control" placeholder="Contoh: 0.98" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">WBP</label>
                    <input type="number" step="0.01" name="wbp" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">LWBP</label>
                    <input type="number" step="0.01" name="lwbp" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">kWh</label>
                    <input type="number" step="0.01" name="kwh" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">kVARh</label>
                    <input type="number" step="0.01" name="kvarh" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Temuan (Opsional)</label>
                    <textarea name="temuan" class="form-control" rows="3" placeholder="Isi jika ada temuan"></textarea>
                </div>

                <div class="row">
                    <div class="col-10">
                        <button type="submit" class="btn btn-success">Simpan Pencatatan</button>
                    </div>
                    <div class="col-2 text-end">
                        <a href="../" class="text-decoration-none btn btn-secondary">< Kembali</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection

@section('js')
<script>
$(document).ready(function () {
    $('#pln-form').on('submit', function (e) {
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
                    html: `<p>Pencatatan berhasil disimpan.</p>`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#pln-form')[0].reset();
                });
            },
            error: function (xhr) {
                let errorMsg = "Terjadi kesalahan.";
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
