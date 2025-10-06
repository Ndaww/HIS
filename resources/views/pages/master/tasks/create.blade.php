@extends('layouts.app')
@section('title', 'Tambah Tugas PM Baru')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Tambah Tugas PM Baru</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Master Data</span> / <a href="{{ route('pm_tasks.index') }}">Tugas PM Shift</a> / Tambah</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Form Tugas I-L-C-T</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm_tasks.store') }}" id="taskForm">
                        @csrf
                        
                        {{-- Field Tipe Equipment --}}
                        <div class="form-group mb-3">
                            <label for="equipment_type_id">Tipe Equipment <span class="text-danger">*</span></label>
                            <select name="equipment_type_id" id="equipment_type_id" class="form-control @error('equipment_type_id') is-invalid @enderror" required>
                                <option value="">Pilih Tipe Equipment</option>
                                @foreach($equipmentTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('equipment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('equipment_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Field Nama Tugas --}}
                        <div class="form-group mb-3">
                            <label for="task_name">Nama Tugas (Task) <span class="text-danger">*</span></label>
                            <input type="text" name="task_name" id="task_name" class="form-control @error('task_name') is-invalid @enderror" value="{{ old('task_name') }}" required>
                            @error('task_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Field Kategori --}}
                        <div class="form-group mb-3">
                            <label for="task_category">Kategori (I/L/C/T) <span class="text-danger">*</span></label>
                            <select name="task_category" id="task_category" class="form-control @error('task_category') is-invalid @enderror" required>
                                @php $oldCategory = old('task_category'); @endphp
                                <option value="I" {{ $oldCategory == 'I' ? 'selected' : '' }}>I (Inspection)</option>
                                <option value="L" {{ $oldCategory == 'L' ? 'selected' : '' }}>L (Level/Lubrication)</option>
                                <option value="C" {{ $oldCategory == 'C' ? 'selected' : '' }}>C (Cleaning)</option>
                                <option value="T" {{ $oldCategory == 'T' ? 'selected' : '' }}>T (Tightening/Condition)</option>
                            </select>
                            @error('task_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        {{-- Field Batas Anomali --}}
                        <div class="form-group mb-3">
                            <label for="anomaly_threshold">Batas Anomali (Standar Master) <span class="text-danger">*</span></label>
                            <textarea name="anomaly_threshold" id="anomaly_threshold" class="form-control @error('anomaly_threshold') is-invalid @enderror" rows="4" required>{{ old('anomaly_threshold') }}</textarea>
                            @error('anomaly_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Hidden Fields (Karena sudah disepakati selalu Shift dan Teknisi Umum) --}}
                        <input type="hidden" name="frequency_type" value="Shift">
                        <input type="hidden" name="responsible_role" value="Teknisi Umum">

                        <hr>
                        <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                        <a href="{{ route('pm_tasks.index') }}" class="btn btn-secondary">Batal</a>
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
        // Tangkap event submit pada form
        $('#taskForm').submit(function(e) {
            e.preventDefault(); // Mencegah submit default
            let form = this; // Simpan referensi form
            
            // Panggil SweetAlert untuk konfirmasi
            Swal.fire({
                title: 'Konfirmasi Penyimpanan Data',
                text: "Apakah Anda yakin data Master Tugas ini sudah benar?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
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
    });
</script>
@endsection