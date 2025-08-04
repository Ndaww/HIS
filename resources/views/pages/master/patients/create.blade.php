@extends('layouts.app')
@section('title', 'Registrasi Pasien')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Registrasi Pasien</h2>
        <div class="breadcrumb" id="breadcrumb"> <span class="me-1">Master / Pasien / </span>  Registrasi Pasien </div>
    </div>

    <div class="card">
        <div class="card-header">Registrasi Pasien</div>

        <div class="card-body">
            <form id="patient-form" action="/master/patients" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Jenis Kelamin</label>
                        <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}">
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>No KTP</label>
                        <input type="text" name="no_ktp" class="form-control @error('no_ktp') is-invalid @enderror" value="{{ old('no_ktp') }}">
                        @error('no_ktp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>No BPJS</label>
                        <input type="text" name="no_bpjs" class="form-control @error('no_bpjs') is-invalid @enderror" value="{{ old('no_bpjs') }}">
                        @error('no_bpjs')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Alamat</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>No Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" id="btn-submit" class="btn btn-primary">
                            Simpan
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
        $('#patient-form').on('submit', function (e) {
            e.preventDefault();

            const form = $(this)[0];
            const formData = new FormData(form);
            const submitBtn = $('#btn-submit');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    submitBtn.attr('disabled', true);
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data pasien berhasil disimpan.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // reset form
                        form.reset();
                    });
                },
                error: function (xhr) {
                    let message = "Terjadi kesalahan.";
                    if (xhr.status === 422) {
                        // Laravel validation error
                        const errors = xhr.responseJSON.errors;
                        message = '<ul>';
                        for (let field in errors) {
                            message += `<li>${errors[field][0]}</li>`;
                        }
                        message += '</ul>';
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: message
                    });
                },
                complete: function () {
                    submitBtn.removeAttr('disabled');
                }
            });
        });
    });
</script>
@endsection