@extends('layouts.app')
@section('title', 'Tambah Ruangan')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Tambah Ruangan</h2>
        <div class="breadcrumb" id="breadcrumb"> <span class="me-1">Master / Pasien / </span>  Tambah Ruangan </div>
    </div>

    <div class="card">
        <div class="card-header">Tambah Ruangan</div>

        <div class="card-body">
            <form id="rooms-form" action="/master/rooms" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Nama Ruangan</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Lantai</label>
                        <select name="floor" id="edit-floor" class="form-control">
                            <option value="Lantai 1">Lantai 1</option>
                            <option value="Lantai 2">Lantai 2</option>
                            <option value="Lantai 3">Lantai 3</option>
                            <option value="Lantai 4">Lantai 4</option>
                            <option value="Lantai 5">Lantai 5</option>
                        </select>
                        @error('floor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label>Kelas</label>
                        <select name="class" id="edit-class" class="form-control">
                            <option value="KELAS 1">Kelas 1</option>
                            <option value="KELAS 2">Kelas 2</option>
                            <option value="KELAS 3">Kelas 3</option>
                            <option value="VIP">VIP</option>
                            <option value="VVIP">VVIP</option>
                        </select>
                        @error('class')
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
        $('#rooms-form').on('submit', function (e) {
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
                        text: 'Data Ruangan berhasil disimpan.',
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