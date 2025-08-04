@extends('layouts.app')
@section('title', 'Validasi GA')
@section('main-content')
    @section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Validasi GA: Kamar Selesai Preventive</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Kamar Kosong </span> / Validasi GA </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Kamar Selesai Preventive</div>
        <div class="card-body">
            <table id="ga-validation-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th width="5px">No</th>
                        <th>Nama Kamar</th>
                        <th>Lantai</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Tanggal Selesai Preventive</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal Validasi --}}
    <div class="modal fade" id="modalValidasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-validasi">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Validasi GA</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <select name="status" class="form-select" required>
                            <option value="ok">Layak (OK)</option>
                            <option value="not_ok">Tidak Layak</option>
                        </select>
                        <textarea name="notes" class="form-control mt-2" placeholder="Catatan jika ditolak (opsional)"></textarea>
                        <input type="hidden" name="room_id" id="room_id">
                        {{-- <p>Apakah kamar ini sudah siap dijual kembali?</p> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Ya, Validasi</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    let table = $('#ga-validation-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("ga.rooms.datatable") }}',
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'floor' },
            { data: 'class' },
            { data: 'status' },
            { data: 'preventive_done_at' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // Buka modal validasi
    $(document).on('click', '.btn-validate', function () {
        let roomId = $(this).data('id');
        $('#room_id').val(roomId);
        new bootstrap.Modal(document.getElementById('modalValidasi')).show();
    });

    // Submit validasi
    $('#form-validasi').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("ga.rooms.validate") }}',
            method: 'POST',
            data: $(this).serialize(),
            beforeSend: () => Swal.showLoading(),
            success: res => {
                Swal.fire('Berhasil!', res.message, 'success');
                $('#modalValidasi').modal('hide');
                table.ajax.reload();
            },
            error: err => {
                Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    });
});
</script>
@endsection