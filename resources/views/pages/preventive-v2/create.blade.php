@extends('layouts.app')
@section('title','Penentuan Target Bulanan Preventive Spesialis')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Penentuan Target Bulanan</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Target Bulanan</div>
    </div>

    {{-- Pesan Sukses (Success Message) --}}
    {{-- @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif --}}

    <div class="row">
        
        {{-- ======================================================= --}}
        {{-- BAGIAN KIRI: FORM PEMBUATAN TARGET BARU (col-md-5) --}}
        {{-- ======================================================= --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Buat Target Baru</h5>
                </div>
                <div class="card-body">
                    {{-- Beri ID pada form untuk memudahkan penargetan di JavaScript --}}
                    <form id="targetForm" action="{{ route('preventive-target.store') }}" method="POST">
                        @csrf

                        {{-- Field Tipe Equipment --}}
                        <div class="mb-3">
                            <label for="equipment_type_id" class="form-label">Tipe Equipment</label>
                            <select name="equipment_type_id" id="equipment_type_id" class="form-select @error('equipment_type_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe Equipment --</option>
                                @foreach($equipments as $eq)
                                    <option value="{{ $eq->id }}" {{ old('equipment_type_id') == $eq->id ? 'selected' : '' }}>
                                        {{ $eq->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('equipment_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Field Bulan --}}
                        <div class="mb-3">
                            <label for="month" class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-select @error('month') is-invalid @enderror" required>
                                @php
                                    $current_month = date('n');
                                    $nama_bulan = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                @endphp
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}" {{ (old('month', $current_month) == $i) ? 'selected' : '' }}>
                                        {{ $nama_bulan[$i] }}
                                    </option>
                                @endfor
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Field Tahun --}}
                        <div class="mb-3">
                            <label for="year" class="form-label">Tahun</label>
                            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror"
                                   value="{{ old('year', date('Y')) }}" required min="{{ date('Y') - 2 }}">
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Field Target Bulanan --}}
                        <div class="mb-3">
                            <label for="target_count" class="form-label">Target Preventive (kali)</label>
                            <input type="number" name="target_count" id="target_count" class="form-control @error('target_count') is-invalid @enderror"
                                   placeholder="Contoh: 50" value="{{ old('target_count') }}" required min="1">
                            @error('target_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tombol submit, diubah menjadi type="button" untuk Swal confirm --}}
                        <button type="button" id="submitTarget" class="btn btn-primary">Simpan Target</button> 
                    </form>
                </div>
            </div>
        </div>
        
        {{-- ======================================================= --}}
        {{-- BAGIAN KANAN: TABEL TARGET YANG SUDAH DIBUAT (col-md-7) --}}
        {{-- ======================================================= --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Target Yang Sudah Dibuat</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Tambahkan ID di sini --}}
                        <table class="table table-striped table-hover" id="targetsTable"> 
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tipe Equipment</th>
                                    <th>Bulan/Tahun</th>
                                    <th>Target (Kali)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($targets as $index => $target)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    {{-- Mengakses relasi equipmentType --}}
                                    <td>{{ $target->equipmentType->name ?? 'Tipe Dihapus' }}</td>
                                    {{-- Menggunakan array $nama_bulan yang sudah didefinisikan --}}
                                    <td>{{ $nama_bulan[$target->month] ?? $target->month }} {{ $target->year }}</td>
                                    <td><span class="badge bg-primary">{{ $target->target_count }}</span></td>
                                    <td>
                                        <a href="{{ route('preventive-target.edit', $target->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        
                                        {{-- FORM DELETE untuk SweetAlert --}}
                                        <form action="{{ route('preventive-target.destroy', $target->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete(this);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                {{-- Baris ini ditampilkan jika $targets kosong --}}
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada target bulanan yang dibuat.</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
    // --- FUNGSI GLOBAL DELETE (dipanggil oleh onsubmit di HTML) ---
    // Dideklarasikan di luar $(document).ready agar bisa diakses oleh 'onsubmit'
    window.confirmDelete = function(form) {
        const row = form.closest('tr');
        const equipmentName = row.cells[1].textContent;
        const monthYear = row.cells[2].textContent;
        const targetCount = row.cells[3].textContent;

        Swal.fire({
            title: 'Konfirmasi Penghapusan Target',
            html: `Anda yakin ingin menghapus target ${targetCount} PM untuk ${equipmentName} pada ${monthYear}? <br><br> Penghapusan ini akan membatalkan semua jadwal PM yang belum dikerjakan untuk periode ini!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false; // Mencegah submit form default
    }


    // --- KONTEN UTAMA DI DALAM JQUERY READY FUNCTION ---
    $(document).ready(function() {
        
        // A. INISIALISASI DATATABLES
        $('#targetsTable').DataTable({
            "order": [[ 2, "desc" ]], 
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] }
            ]
        });

        // B. LOGIKA SWAL CONFIRM SUBMIT (Form Kiri)
        const submitButton = document.getElementById('submitTarget');
        const form = document.getElementById('targetForm');

        if (submitButton && form) {
            submitButton.addEventListener('click', function (e) {
                if (!form.reportValidity()) { return; }

                const equipmentName = form.querySelector('#equipment_type_id option:checked').textContent;
                const monthName = form.querySelector('#month option:checked').textContent;
                const year = form.querySelector('#year').value;
                const targetCount = form.querySelector('#target_count').value;

                Swal.fire({
                    title: 'Konfirmasi Simpan Target',
                    html: `Anda akan menetapkan target ${targetCount} kali Preventive Maintenance untuk Tipe ${equipmentName} pada Bulan ${monthName} Tahun ${year}.<br><br>Target ini akan menggenerasi jadwal untuk ${targetCount} unit alat.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
        
        // C. LOGIKA SWAL NOTIFIKASI POST-REDIRECT (SUKSES/ERROR)
        
        const swalSuccessMessage = "{!! Session::get('swal_success') !!}";
        const swalErrorMessage = "{!! Session::get('swal_error') !!}";

        if (swalSuccessMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: swalSuccessMessage,
                showConfirmButton: false,
                timer: 4000 
            });
        }
        
        if (swalErrorMessage) {
             Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: swalErrorMessage,
                showConfirmButton: true,
            });
        }
    }); // <-- Pastikan ini adalah kurung kurawal penutup untuk $(document).ready(function() {
</script>
@endsection