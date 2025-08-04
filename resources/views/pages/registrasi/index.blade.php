@extends('layouts.app')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Daftar Pasien</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Sistem Kamar</span> / Daftar Pasien </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Pasien Terdaftar</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filter_gender">Jenis Kelamin</label>
                    <select class="form-control" id="filter_gender">
                        <option value="">-- Semua --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_room_status">Status Kamar</label>
                    <select class="form-control" id="filter_room_status">
                        <option value="">-- Semua --</option>
                        <option value="kosong">Kosong</option>
                        <option value="terisi">Terisi</option>
                        <option value="preventive">Preventive</option>
                    </select>
                </div>
                <div class="col-md-3 align-self-end">
                    <button id="filter" class="btn btn-primary"> <i class="ri ri-filter-line"></i>Filter</button>
                    <button id="reset" class="btn btn-secondary"><i class="ri ri-refresh-line"></i> Reset</button>
                </div>
            </div>

            <table id="patients-table" class="table table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Gender</th>
                        <th>Tanggal Lahir</th>
                        <th>No KTP</th>
                        <th>No BPJS</th>
                        <th>Kamar</th>
                        <th>Check-in</th>
                        <th>Status Kamar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        let table = $('#patients-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route('patients.index.data') }}',
                data: function (d) {
                    d.gender = $('#filter_gender').val();
                    d.room_status = $('#filter_room_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name' },
                { data: 'gender' },
                { data: 'birth_date' },
                { data: 'no_ktp' },
                { data: 'no_bpjs' },
                { data: 'room' },
                { data: 'checkin_at' },
                { data: 'room_status' },
                { data: 'action', orderable: false, searchable: false },
            ]
        });

        $('#filter').on('click', function () {
            table.ajax.reload();
        });

        $('#reset').on('click', function () {
            $('#filter_gender').val('');
            $('#filter_room_status').val('');
            table.ajax.reload();
        });

        $(document).on('submit', '.form-checkout', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Checkout Pasien?',
                text: "Ruangan akan masuk status preventive.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, Checkout'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
