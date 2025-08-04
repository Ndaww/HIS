@extends('layouts.app')
@section('title', 'Booking Kamar')
@section('main-content')
    <div class="header-breadcrumb">
    <h2 id="page-title">Booking Kamar</h2>
    <div class="breadcrumb" id="breadcrumb"> <span>Kamar Kosong </span> / Booking Kamar </div>
</div>

<div class="card mb-4">
    <div class="card-header">Form Booking</div>
    <div class="card-body">
        <form id="booking-form" action="{{ route('bookings.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Pasien</label>
                    <select class="form-control select2" name="patient_id" required>
                        <option value="">-- Pilih Pasien --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} / {{ $p->no_ktp }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Kamar</label>
                    <select class="form-control select2" name="room_id" required>
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} - {{ $r->class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Check-in</label>
                    <input type="datetime-local" name="checkin_at" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Booking</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Booking</div>
    <div class="card-body">
        
        <table class="table table-bordered w-100" id="bookings-table">
            <thead>
                <tr>
                    <th width="5px">No</th>
                    <th>Pasien</th>
                    <th>Kamar</th>
                    <th>Kelas</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th width="11%">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    $('.select2').select2();

    const table = $('#bookings-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('bookings.data') }}",
        lengthMenu: [10, 25, 50, 100, -1],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable: false },
            { data: 'patient' },
            { data: 'room' },
            { data: 'class' },
            { data: 'checkin' },
            { data: 'checkout' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    $('#booking-form').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: res.message,
                    confirmButtonText: 'OK'
                });

                $('#booking-form')[0].reset();
                $('.select2').val(null).trigger('change');
                table.ajax.reload();
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan.';

                if (xhr.status === 422) {
                    // Handle validasi laravel
                    if (xhr.responseJSON?.errors) {
                        message = '<ul>';
                        Object.values(xhr.responseJSON.errors).forEach(err => {
                            message += `<li>${err[0]}</li>`;
                        });
                        message += '</ul>';
                    } else if (xhr.responseJSON?.message) {
                        // Contoh: kamar tidak tersedia, pasien masih di kamar
                        message = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: message
                });
            }
        });
    });

    // cancel
    $('#bookings-table').on('click', '.btn-cancel', function () {
        const bookingId = $(this).data('id');

        Swal.fire({
            title: 'Batalkan Booking?',
            text: "Apakah kamu yakin ingin membatalkan booking ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/bookings/' + bookingId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        $('#bookings-table').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        let msg = 'Gagal membatalkan booking.';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    });

    // checkout
    $('#bookings-table').on('click', '.btn-checkout', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Checkout Pasien?',
            text: "Kamar akan masuk ke tahap preventive!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Checkout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/bookings/${id}/checkout`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire('Sukses!', res.message, 'success');
                        $('#bookings-table').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        let msg = 'Gagal checkout.';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            }
        });
    });

});
</script>
@endsection