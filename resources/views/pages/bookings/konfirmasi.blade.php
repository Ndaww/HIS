@extends('layouts.app')
@section('title', 'Konfirmasi Perawat')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Konfirmasi Perawat</h2>
        <div class="breadcrumb" id="breadcrumb"> <span>Sistem Kamar</span> / Konfirmasi Perawat </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Kamar Menunggu Konfirmasi</div>
        <div class="card-body">
            <div class="mb-2">
                <button class="btn btn-primary" id="bulk-konfirmasi"><i class="ri-check-double-line"></i> Konfirmasi Terpilih</button>
            </div>
            <table id="nurse-confirm-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th width="5px"><input type="checkbox" id="check-all"></th>
                        <th>No</th>
                        <th>Nama Kamar</th>
                        <th>Lantai</th>
                        <th>Kelas</th>
                        <th>Status GA</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal Konfirmasi --}}
    <div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-konfirmasi" action="{{ route("nurse.confirm.store") }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Perawat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="room_id" id="room_id">
                        <p>Apakah kamar ini sudah siap digunakan pasien?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Ya, Siap</button>
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
    let table = $('#nurse-confirm-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("nurse.confirm.datatable") }}',
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name' },
            { data: 'floor' },
            { data: 'class' },
            { data: 'ga_status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // Check/uncheck all
    $('#check-all').on('click', function () {
        $('.row-checkbox').prop('checked', this.checked);
    });

    // Konfirmasi satuan (sebelumnya)
    $(document).on('click', '.btn-konfirmasi', function () {
        $('#room_id').val($(this).data('id'));
        new bootstrap.Modal(document.getElementById('modalKonfirmasi')).show();
    });

    // Submit bulk konfirmasi
    $('#bulk-konfirmasi').on('click', function () {
        const selected = $('.row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) {
            return Swal.fire('Oops!', 'Tidak ada kamar yang dipilih.', 'warning');
        }

        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Terpilih?',
            text: `Yakin ingin mengonfirmasi ${selected.length} kamar sebagai siap?`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Konfirmasi'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("nurse.confirm.store") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        room_ids: selected
                    },
                    beforeSend: () => Swal.showLoading(),
                    success: res => {
                        Swal.fire('Berhasil!', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: err => {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    });
});
</script>

@endsection