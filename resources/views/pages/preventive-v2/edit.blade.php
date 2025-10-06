@extends('layouts.app')
@section('title','Edit Target Bulanan Preventive Spesialis')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Edit Target Bulanan</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Target Bulanan / Edit</div>
    </div>

    @php
        // Definisi array nama bulan (agar bisa diakses oleh Blade di luar script JS)
        $nama_bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Target: {{ $target->equipmentType->name ?? 'Tipe Dihapus' }} ({{ $nama_bulan[$target->month] ?? $target->month }}/{{ $target->year }})</h5>
        </div>
        <div class="card-body">
            
            {{-- Action diarahkan ke rute update dengan method PUT --}}
            <form id="targetForm" action="{{ route('preventive-target.update', $target->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Method Spoofing untuk UPDATE --}}

                {{-- Field Tipe Equipment (Disabled, hanya untuk tampilan) --}}
                <div class="mb-3">
                    <label for="equipment_type_id" class="form-label">Tipe Equipment</label>
                    <select name="equipment_type_id" id="equipment_type_id" class="form-select" disabled>
                        <option value="{{ $target->equipment_type_id }}" selected>
                            {{ $target->equipmentType->name ?? 'Tipe Dihapus' }}
                        </option>
                    </select>
                    {{-- Input hidden agar data tetap terkirim --}}
                    <input type="hidden" name="equipment_type_id" value="{{ $target->equipment_type_id }}">
                </div>

                {{-- Field Bulan (Disabled, hanya untuk tampilan) --}}
                <div class="mb-3">
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select" disabled>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ $target->month == $i ? 'selected' : '' }}>
                                {{ $nama_bulan[$i] }}
                            </option>
                        @endfor
                    </select>
                    <input type="hidden" name="month" value="{{ $target->month }}">
                </div>

                {{-- Field Tahun (Disabled, hanya untuk tampilan) --}}
                <div class="mb-3">
                    <label for="year" class="form-label">Tahun</label>
                    <input type="number" name="year" id="year" class="form-control"
                           value="{{ $target->year }}" disabled>
                    <input type="hidden" name="year" value="{{ $target->year }}">
                </div>

                {{-- Field Target Bulanan (Ini yang diizinkan untuk diubah) --}}
                <div class="mb-3">
                    <label for="target_count" class="form-label">Target Preventive (kali)</label>
                    <input type="number" name="target_count" id="target_count" class="form-control @error('target_count') is-invalid @enderror"
                           placeholder="Contoh: 50" 
                           value="{{ old('target_count', $target->target_count) }}" required min="1">
                    @error('target_count')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="button" id="submitTarget" class="btn btn-success">Update Target</button>
                <a href="{{ route('preventive-target.create') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submitButton = document.getElementById('submitTarget');
        const form = document.getElementById('targetForm');

        if (submitButton && form) {
            submitButton.addEventListener('click', function (e) {
                if (!form.reportValidity()) {
                    return; 
                }

                // Ambil data untuk Swal
                const oldTarget = '{{ $target->target_count }}';
                const newTarget = form.querySelector('#target_count').value;
                const equipmentName = '{{ $target->equipmentType->name ?? 'Tipe Equipment' }}';
                
                // Menggunakan variabel $nama_bulan yang didefinisikan di Blade
                const monthYear = '{{ $nama_bulan[$target->month] ?? $target->month }} {{ $target->year }}';

                Swal.fire({
                    title: 'Konfirmasi Update Target',
                    html: `Anda akan mengubah target PM untuk ${equipmentName} pada ${monthYear} dari ${oldTarget} menjadi ${newTarget} kali.<br><br>Apakah Anda yakin? (Perubahan ini memengaruhi jadwal yang sudah dibuat).`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Update!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
        
        // --- Logika Swal Notifikasi Sukses/Error (POST-REDIRECT) ---
        const swalSuccessMessage = "{{ Session::get('swal_success') }}";
        const swalErrorMessage = "{{ Session::get('swal_error') }}";

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
    });
</script>
@endsection