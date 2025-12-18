@extends('layouts.app')
@section('title','Facility Tour - Input')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Facility Tour</h2>
        <div class="breadcrumb" id="breadcrumb">
            <span>Facility</span> / Input Facility Tour
        </div>
    </div>

    <div class="card">
        <div class="card-header">Form Facility Tour</div>
        <div class="card-body">

            <form id="facility-tour-form" action="{{ route('facility-tour.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- RUANGAN --}}
                <div class="mb-3">
                    <label class="form-label">Pilih Ruangan</label>
                    <select name="room_id" id="room_id" class="form-control" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->floor }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- DAFTAR EQUIPMENT (AUTO LOAD) --}}
                <div id="equipment-list" class="mt-4">
                    <h5 class="text-muted text-center">Silakan pilih ruangan terlebih dahulu</h5>
                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan Facility Tour</button>
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

    // LOAD EQUIPMENT BERDASARKAN RUANGAN
    $('#room_id').on('change', function () {
        let roomId = $(this).val();

        if (!roomId) {
            $('#equipment-list').html('<h5 class="text-muted text-center">Silakan pilih ruangan terlebih dahulu</h5>');
            return;
        }

        $.ajax({
            url: "/facility-tour/get-equipment/" + roomId,
            method: "GET",
            beforeSend: function () {
                $('#equipment-list').html('<p class="text-center text-muted">Memuat data...</p>');
            },
            success: function (response) {
                let html = `
                    <h5 class="mb-3">Checklist Equipment</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Item</th>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Foto Temuan</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                response.forEach(equip => {
                    html += `
                        <tr>
                            <td>${equip.name}</td>
                            <td>${equip.serial_number}</td>

                            <td>
                                <select name="status[${equip.id}]" class="form-select" required>
                                    <option value="OK">OK</option>
                                    <option value="NOT OK">NOT OK</option>
                                </select>
                            </td>

                            <td>
                                <input type="text" name="notes[${equip.id}]" class="form-control" placeholder="Opsional">
                            </td>

                            <td>
                                <input type="file" name="photos[${equip.id}]" class="form-control" accept="image/*">
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                $('#equipment-list').html(html);
            }
        });
    });


    // SUBMIT FORM VIA AJAX
    $('#facility-tour-form').on('submit', function (e) {
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
                    html: `<p>Facility Tour berhasil disimpan.</p>`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#facility-tour-form')[0].reset();
                    $('#equipment-list').html('<h5 class="text-muted text-center">Silakan pilih ruangan terlebih dahulu</h5>');
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
