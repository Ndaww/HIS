@extends('layouts.app')
@section('title', 'Master Tugas PM Per-Shift')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Master Tugas PM Per-Shift</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Master Data</span> / Tugas PM Shift</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Tugas I-L-C-T Teknisi Umum</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('pm_tasks.create') }}" class="btn btn-primary mb-3">
                        <i class="ri-add-large-fill"> </i> Tambah Tugas Baru
                    </a>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="masterPmTasksTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th> 
                                    <th>Equipment</th>
                                    <th>Tugas (Task Name)</th>
                                    <th style="width: 10%;">Kategori</th>
                                    <th>Batas Anomali (Threshold)</th>
                                    <th style="width: 10%;">Frekuensi</th>
                                    {{-- <th style="width: 15%;">Role Penanggung Jawab</th> --}}
                                    <th style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data akan diisi secara otomatis oleh DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Tambah Tugas PM Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="taskForm" method="POST" action=""> {{-- Hapus action, akan diisi JS --}}
                @csrf
                {{-- Input Hidden untuk Method Spoofing (diisi saat EDIT) --}}
                <input type="hidden" name="_method" value="POST" id="methodField"> 
                <input type="hidden" name="id" id="task_id"> {{-- Digunakan untuk identifikasi saat update --}}

                <div class="modal-body">
                    {{-- ... (Sisa form group Anda seperti sebelumnya) ... --}}
                    
                    <div class="form-group">
                        <label for="equipment_type_id">Tipe Equipment</label>
                        <select name="equipment_type_id" id="equipment_type_id" class="form-control" required>
                            <option value="">Pilih Tipe Equipment</option>
                            @foreach($equipmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback equipment_type_id_error"></div>
                    </div>
                    
                    {{-- ... (lanjutan form) ... --}}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#masterPmTasksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pm_tasks.data') }}", 
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // Nomor urut
                { data: 'equipment_name', name: 'equipmentType.name' }, 
                { data: 'task_name', name: 'task_name' },
                { data: 'category', name: 'task_category', className: 'text-center' },
                { data: 'anomaly_threshold', name: 'anomaly_threshold' },
                { data: 'frequency_type', name: 'frequency_type' },
                // { data: 'responsible_role', name: 'responsible_role' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });

        $(document).on('click', '.btn-delete', function() {
            let taskId = $(this).data('id');
            
            Swal.fire({
                title: 'Yakin Hapus Data?',
                text: "Anda akan menghapus tugas ini secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Merah
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // Tampilkan loading saat request dikirim
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Lakukan AJAX DELETE
                    $.ajax({
                        url: `/master/task/${taskId}`, 
                        type: 'POST', // Gunakan POST karena form submission (Method Spoofing)
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE' // Spoofing method ke DELETE
                        },
                        dataType: 'json',
                        success: function(response) {
                            // Tutup loading
                            Swal.close(); 
                            
                            // Refresh DataTables
                            $('#masterPmTasksTable').DataTable().ajax.reload(null, false); 

                            // Tampilkan pesan sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2500
                            });
                        },
                        error: function(xhr) {
                            // Tampilkan pesan error
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Hapus!',
                                text: 'Terjadi kesalahan saat menghapus data.',
                            });
                        }
                    });
                }
            });
        

        });
    });

        @if (session('success_message'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success_title') ?? 'Berhasil!' }}',
                html: '{{ session('success_message') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif
        
        @if (session('error_message'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error_title') ?? 'Gagal!' }}',
                html: '{{ session('error_message') }}',
            });
        @endif

</script>


@endsection