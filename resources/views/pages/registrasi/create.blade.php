@extends('layouts.app')
@section('title','Registrasi - Registrasi Pasien Rawat Inap')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Registrasi Pasien Rawat Inap</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Registrasi</span> / Registrasi Pasien Rawat Inap</div>
    </div>

    <div class="card">
        <div class="card-header">Registrasi Pasien Rawat Inap</div>
        <div class="card-body">
            <form id="form-registrasi" action="{{ route('registrasi.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select name="gender" id="gender" class="form-select" required>
                            <option value="">Pilih...</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="birth_date" class="form-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="birth_date" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="no_ktp" class="form-label">No KTP</label>
                        <input type="text" name="no_ktp" id="no_ktp" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="no_bpjs" class="form-label">No BPJS</label>
                        <input type="text" name="no_bpjs" id="no_bpjs" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="text" name="phone" id="phone" class="form-control">
                </div>

                <div class="mb-4">
                    <label for="room_id" class="form-label">Pilih Kamar Kosong</label>
                    <select name="room_id" id="room_id" class="form-select" required>
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->floor }} - {{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="btn-submit" class="btn btn-primary">Simpan</button>
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
    $('#room_id').select2({
        placeholder: 'Pilih Ruangan',
        width: '100%',
    });

    document.getElementById('btn-submit').addEventListener('click', function () {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Apakah data sudah benar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, simpan!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-registrasi').submit();
            }
        });
    });
});
</script>
@endsection
