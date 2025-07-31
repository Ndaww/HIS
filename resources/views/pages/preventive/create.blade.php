@extends('layouts.app')
@section('title','Preventive - Buat Jadwal Preventive')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Buat Jadwal Preventive</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Buat Jadwal Preventive</div>
    </div>

    <div class="card">
        <div class="card-header"><h4>Buat Jadwal Preventif Maintenance</h4></div>
        <div class="card-body">
            <form action="{{ route('preventive.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="col">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Pilih Ruangan (bisa lebih dari 1)</label>
                    <select name="room_ids[]" id="room-select" class="form-control" multiple required>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->floor }} - {{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Pilih Equipment (bisa lebih dari 1)</label>
                    <select name="equipment_ids[]" id="equipment-select" class="form-control" multiple required>
                        <!-- Akan diisi via AJAX -->
                    </select>
                </div>

                <button id="submit-btn" type="button" class="btn btn-primary">Buat Jadwal</button>
            </form>
        </div>
    
</div>


@endsection

@section('js')
<script>
$(document).ready(function () {
    $('#room-select').select2({
        placeholder: 'Pilih Ruangan',
        width: '100%',
    });

    $('#equipment-select').select2({
        placeholder: 'Pilih Equipment',
        width: '100%',
    });

    $('#room-select').on('change', function () {
        let roomIds = $(this).val();

        if (roomIds.length > 0) {
            $.ajax({
                url: "{{ route('ajax.getEquipmentByRooms') }}",
                type: "GET",
                data: { room_ids: roomIds },
                success: function (response) {
                    let options = `<option value="all">-- Pilih Semua Equipment --</option>`;
                    response.forEach(eq => {
                        options += `<option value="${eq.id}">${eq.name} - SN: ${eq.serial_number}</option>`;
                    });
                    $('#equipment-select').html(options).val(null).trigger('change');
                }
            });
        } else {
            $('#equipment-select').html('').trigger('change');
        }
    });

    $('#equipment-select').on('select2:select', function (e) {
        if (e.params.data.id === "all") {
            let allValues = [];
            $('#equipment-select option').each(function () {
                let val = $(this).val();
                if (val !== "all") {
                    allValues.push(val);
                }
            });

            $('#equipment-select').val(allValues).trigger('change');

            setTimeout(() => {
                const selected = $('#equipment-select').val().filter(id => id !== "all");
                $('#equipment-select').val(selected).trigger('change');
            }, 0);
        }
    });
});

    $('#submit-btn').on('click', function () {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah kamu yakin ingin membuat jadwal preventive maintenance ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = $('form').serialize();

                $.ajax({
                    url: "{{ route('preventive.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Jadwal berhasil dibuat.'
                        }).then(() => {
                            window.location.reload(); 
                        });
                    },
                    error: function (xhr) {
                        let errMsg = 'Terjadi kesalahan.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errMsg
                        });
                    }
                });
            }
        });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Jadwal Berhasil Dibuat',
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