@extends('layouts.app')
@section('title', 'Edit Tugas PM: ' . $task->task_name)

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Edit Tugas PM</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Master Data</span> / <a href="{{ route('pm_tasks.index') }}">Tugas PM Shift</a> / Edit</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Form Edit Tugas: {{ $task->task_name }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm_tasks.update', $task->id) }}" id="taskForm">
                        @csrf
                        @method('PUT') {{-- Metode untuk UPDATE --}}
                        
                        {{-- Field Tipe Equipment --}}
                        <div class="form-group mb-3">
                            <label for="equipment_type_id">Tipe Equipment <span class="text-danger">*</span></label>
                            <select name="equipment_type_id" id="equipment_type_id" class="form-control @error('equipment_type_id') is-invalid @enderror" required>
                                <option value="">Pilih Tipe Equipment</option>
                                @foreach($equipmentTypes as $type)
                                    @php $selected = (old('equipment_type_id', $task->equipment_type_id) == $type->id) ? 'selected' : ''; @endphp
                                    <option value="{{ $type->id }}" {{ $selected }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('equipment_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Field Nama Tugas --}}
                        <div class="form-group mb-3">
                            <label for="task_name">Nama Tugas (Task) <span class="text-danger">*</span></label>
                            <input type="text" name="task_name" id="task_name" class="form-control @error('task_name') is-invalid @enderror" value="{{ old('task_name', $task->task_name) }}" required>
                            @error('task_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Field Kategori --}}
                        <div class="form-group mb-3">
                            <label for="task_category">Kategori (I/L/C/T) <span class="text-danger">*</span></label>
                            <select name="task_category" id="task_category" class="form-control @error('task_category') is-invalid @enderror" required>
                                @php $oldCategory = old('task_category', $task->task_category); @endphp
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
                            <textarea name="anomaly_threshold" id="anomaly_threshold" class="form-control @error('anomaly_threshold') is-invalid @enderror" rows="4" required>{{ old('anomaly_threshold', $task->anomaly_threshold) }}</textarea>
                            @error('anomaly_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Hidden Fields (Wajib ada untuk update) --}}
                        <input type="hidden" name="frequency_type" value="{{ old('frequency_type', $task->frequency_type) }}">
                        <input type="hidden" name="responsible_role" value="{{ old('responsible_role', $task->responsible_role) }}">

                        <hr>
                        <button type="submit" class="btn btn-warning">Perbarui Tugas</button>
                        <a href="{{ route('pm_tasks.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
{{-- Sertakan skrip Swal konfirmasi submit yang sama dari create.blade.php di sini --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Tangkap event submit pada form
        $('#taskForm').submit(function(e) {
            e.preventDefault(); 
            let form = this; 
            
            // Panggil SweetAlert untuk konfirmasi
            Swal.fire({
                title: 'Konfirmasi Perubahan Data',
                text: "Apakah Anda yakin ingin menyimpan perubahan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107', // Warna kuning (warning)
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Perbarui!',
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