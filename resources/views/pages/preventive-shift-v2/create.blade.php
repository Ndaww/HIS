@extends('layouts.app')
@section('title', 'Mulai Ronde PM Baru')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Mulai Ronde PM Baru</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Ronde PM / Mulai</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8 offset-md-2">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Informasi Ronde Shift</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm_rounds.store') }}" id="roundForm">
                        @csrf
                        
                        {{-- Shift Saat Ini (Otomatis dari Controller) --}}
                        <div class="form-group mb-3">
                            <label for="shift_name">Shift Saat Ini</label>
                            <input type="text" class="form-control" value="{{ $currentShift }} (Waktu Server)" readonly>
                            <input type="hidden" name="shift_name" value="{{ $currentShift }}">
                        </div>
                        
                        {{-- Pilihan Teknisi --}}
                        <div class="form-group mb-3">
                            <label for="technician_id">Teknisi Bertugas <span class="text-danger">*</span></label>
                            <select name="technician_id" id="technician_id" class="form-control @error('technician_id') is-invalid @enderror" required>
                                <option value="">Pilih Teknisi</option>
                                @foreach($technicians as $tech)
                                    {{-- Coba pre-select jika ID user saat ini cocok dengan Teknisi --}}
                                    <option value="{{ $tech->id }}" {{ (Auth::id() == $tech->id || old('technician_id') == $tech->id) ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('technician_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary btn-lg">
                            Mulai Ronde & Isi Hasil Cek
                        </button>
                        <a href="{{ route('pm_rounds.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Logika Swal Konfirmasi Create/Store
        $('#roundForm').submit(function(e) {
            e.preventDefault(); 
            let form = this; 
            
            // Verifikasi input dasar (selain validasi Laravel)
            if ($('#technician_id').val() === '') {
                Swal.fire('Perhatian', 'Anda harus memilih Teknisi yang bertugas.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Mulai Ronde PM?',
                text: "Waktu mulai ronde akan dicatat sekarang, dan Anda akan diarahkan ke halaman eksekusi.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', 
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Mulai!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading screen sebelum submit
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit(); 
                }
            });
        });
        
        // Logika Swal Error dari session (jika Controller redirect back karena error)
        @if (session('error_message'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error_title') ?? 'Gagal!' }}',
                text: '{{ session('error_message') }}',
            });
        @endif
    });
</script>
@endsection