@extends('layouts.app')
@section('title','Preventive - Tugas Preventif Saya')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Tugas Preventif Saya</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Tugas Preventif Saya</div>
    </div>

    <div class="card">
        <div class="card-header">
            Tugas Preventif Saya
        </div>
        <div class="card-body">
            @if($tasks->isEmpty())
                <div class="alert alert-info">Tidak ada tugas preventif untuk Anda hari ini.</div>
            @else
                <div class="list-group">
                    @foreach ($tasks as $task)
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $task->equipment->name }}</strong> - {{ $task->equipment->serial_number }}<br>
                                    <small>{{ $task->room->floor }} - {{ $task->room->name }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $task->status === 'pending' ? 'warning' : 'info' }}">
                                        {{ ucfirst($task->status) }}
                                    </span><br>
                                    <small>{{ \Carbon\Carbon::parse($task->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($task->end_date)->format('d M') }}</small>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        </div>
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