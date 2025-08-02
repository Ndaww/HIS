@extends('layouts.app')
@section('title','Preventive - Form Tindakan Preventif')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Form Tindakan Preventif</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Form Tindakan Preventif</div>
    </div>

    <div class="card">
        <div class="card-header">
            Form Tindakan Preventif
        </div>
        <div class="card-body">
            <h4 class="mb-3">Form Tindakan Preventif</h4>

            <div class="mb-3">
                <strong>Ruangan:</strong> {{ $task->room->floor }} - {{ $task->room->name }} <br>
                <strong>Equipment:</strong> {{ $task->equipment->name }} ({{ $task->equipment->serial_number }}) <br>
                <strong>Periode:</strong> {{ \Carbon\Carbon::parse($task->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($task->end_date)->format('d M') }}
            </div>

            <form method="POST" action="{{ route('preventive-task.store-task', $task->id) }}">
                @csrf

                @foreach ($task->details as $detail)
                {{-- @dd($detail->preventiveType->equipmentPreventive); --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <label>
                                <input type="checkbox" name="actions[{{ $detail->id }}][status]" value="done"
                                    {{ $detail->status === 'done' ? 'checked' : '' }}>
                                <strong>{{ $detail->preventiveType->equipmentPreventive->name }}</strong>
                            </label>
                            <textarea class="form-control mt-2" name="actions[{{ $detail->id }}][note]" placeholder="Catatan (opsional)...">{{ $detail->note }}</textarea>
                        </div>
                    </div>
                @endforeach

                <button class="btn btn-primary">Simpan</button>
            </form>
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
                            text: 'Tugas berhasil disubmit.'
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
            title: 'Berhasil'
            text: '{{ session('success') }}'
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
