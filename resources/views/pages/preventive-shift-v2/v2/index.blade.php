@extends('layouts.app')
@section('title', 'Manajemen Pembagian Tugas Per Shift')

@section('main-content')
<style>
   /* CSS untuk menyamarkan radio button menjadi ikon centang */
.shift-checkbox-replacement {
    display: block; /* Agar menempati seluruh sel tabel */
    width: 100%;
    height: 100%;
    cursor: pointer;
    position: relative;
}

.shift-checkbox-replacement input[type="radio"] {
    /* Sembunyikan radio button asli */
    position: absolute;
    clip: rect(0, 0, 0, 0);
    pointer-events: none;
    opacity: 0;
}

.shift-checkbox-replacement label {
    display: block;
    width: 100%;
    height: 100%;
    padding: 8px 0; /* Memberi ruang sentuh */
    position: relative;
}

.shift-checkbox-replacement label:before {
    /* Desain kotak centang (kotak kosong) */
    content: '\25FB'; /* Kotak kosong Unicode */
    font-size: 1.2rem;
    color: #ccc;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    transition: color 0.2s ease, transform 0.2s ease;
}

.shift-checkbox-replacement input[type="radio"]:checked + label:before {
    /* Tampilan saat tercentang (Ikon centang) */
    content: '\2714'; /* Simbol centang Unicode */
    color: #28a745; /* Warna hijau success */
    font-size: 1.3rem;
    transform: translate(-50%, -50%) scale(1.1); /* Sedikit efek zoom */
}
</style>

    <div class="header-breadcrumb">
        <h2 id="page-title">Pembagian Tugas PM Per Shift</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Pembagian Jadwal</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-white shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Atur Shift Penanggung Jawab</h5>
                    <small>Penjadwalan yang Anda buat hanya berlaku untuk periode {{ $months[$targetMonth] ?? 'N/A' }} {{ $targetYear }}.</small>
                </div>
                <div class="card-body">
                    
                    {{-- Pilihan Bulan & Tahun --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="select_month">Pilih Bulan</label>
                                <select id="select_month" class="form-control">
                                    @foreach ($months as $num => $name)
                                        <option value="{{ $num }}" {{ $num == $targetMonth ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="select_year">Pilih Tahun</label>
                                <select id="select_year" class="form-control">
                                    @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" {{ $year == $targetYear ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pm_schedule.store') }}" id="scheduleForm">
                        @csrf
                        
                        {{-- Input tersembunyi untuk mengirim periode saat submit --}}
                        <input type="hidden" name="schedule_month" id="schedule_month_hidden" value="{{ $targetMonth }}">
                        <input type="hidden" name="schedule_year" id="schedule_year_hidden" value="{{ $targetYear }}">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="scheduleTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Equipment Type</th>
                                        <th>Nama Tugas</th>
                                        <th>Kategori</th>
                                        <th class="text-center">Shift 1</th>
                                        <th class="text-center">Shift 2</th>
                                        <th class="text-center">Shift 3</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($tasks as $task)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $task->equipmentType->name ?? 'N/A' }}</td>
                                            <td>{{ $task->task_name }}</td>
                                            <td class="text-center">
                                                <span class="badge 
                                                    {{ match ($task->task_category) {'I' => 'bg-info', 'L' => 'bg-success', 'C' => 'bg-warning', 'T' => 'bg-danger', default => 'bg-secondary', } }} 
                                                    text-white">{{ $task->task_category }}</span>
                                            </td>
                                            
                                            {{-- Kolom Pembagian Shift (Radio Button) --}}
                                            @foreach ($shifts as $shift)
                                                <td class="text-center">
                                                    <div class="shift-checkbox-replacement">
                                                        <input type="radio" 
                                                            id="assignment_{{ $task->id }}_{{ Str::slug($shift) }}" 
                                                            name="assignment[{{ $task->id }}]" 
                                                            value="{{ $shift }}"
                                                            {{ (isset($currentSchedule[$task->id]) && $currentSchedule[$task->id] == $shift) ? 'checked' : '' }}>
                                                            
                                                        {{-- Label bertindak sebagai kotak centang yang dapat diklik --}}
                                                        <label for="assignment_{{ $task->id }}_{{ Str::slug($shift) }}"></label>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Simpan Pembagian Jadwal
                            </button>
                        </div>
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
        // ------------------------------------------
        // LOGIKA SESSION FLASH MESSAGES (BARU)
        // ------------------------------------------
        @if (session('success_title') && session('success_message'))
            Swal.fire({
                icon: 'success',
                title: '{{ session('success_title') }}',
                text: '{{ session('success_message') }}',
                showConfirmButton: false,
                timer: 3000
            });
        @endif

        @if (session('error_title') && session('error_message'))
            Swal.fire({
                icon: 'error',
                title: '{{ session('error_title') }}',
                text: '{{ session('error_message') }}',
            });
        @endif
        
        // ------------------------------------------
        // LOGIKA PERUBAHAN BULAN/TAHUN
        // ------------------------------------------
        $('#select_month, #select_year').on('change', function() {
            let selectedMonth = $('#select_month').val();
            let selectedYear = $('#select_year').val();
            
            // Redirect ke halaman yang sama dengan parameter query baru
            window.location.href = '{{ route('pm_schedule.index') }}' + '?month=' + selectedMonth + '&year=' + selectedYear;
        });

        // ------------------------------------------
        // LOGIKA SUBMIT
        // ------------------------------------------
        $('#scheduleForm').submit(function(e) {
            e.preventDefault();
            let form = this;
            
            // Sinkronkan nilai dropdown ke hidden input sebelum submit
            $('#schedule_month_hidden').val($('#select_month').val());
            $('#schedule_year_hidden').val($('#select_year').val());
            
            Swal.fire({
                title: 'Konfirmasi Penjadwalan?',
                text: "Penjadwalan yang lama untuk periode {{ $months[$targetMonth] ?? 'N/A' }} {{ $targetYear }} akan ditimpa dengan yang baru.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Jadwal!',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    form.submit();
                }
            });
        });
    });
</script>

@endsection