@extends('layouts.app') 

@section('title', 'Formulir Pelaksanaan PM')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Pelaksanaan PM Jadwal: {{ $schedule->id }}</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive V2</span> / Form Eksekusi</div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('pm.store') }}">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

        {{-- BAGIAN A: INFORMASI DASAR & WAKTU --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">Informasi & Waktu Pelaksanaan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Equipment:</label>
                        <p class="form-control-static">{{ $schedule->equipment->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Teknisi:</label>
                        <p class="form-control-static">{{ $schedule->technician->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Tgl. Dijadwalkan:</label>
                        <p class="form-control-static">{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d M Y') }}</p>
                    </div>
                </div>
                
                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label for="start_time">Waktu Mulai: <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" 
                            value="{{ old('start_time', $schedule->start_time) }}" required>
                        @error('start_time')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_time">Waktu Selesai: <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" 
                            value="{{ old('end_time', $schedule->end_time) }}" required>
                        @error('end_time')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN B: DETAIL TUGAS/CHECKLIST --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning text-dark d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Checklist Tugas PM (Input Fleksibel)</h6>
                {{-- Tombol dengan type="button" sudah benar, tapi preventDefault() di JS akan memastikan --}}
                <button type="button" class="btn btn-sm btn-secondary text-white" id="addRowBtn">
                    <i class="ri-menu-add-fill"></i> Tambah Baris Tugas
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0" id="tasksTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="30%">Deskripsi Tugas <span class="text-danger">*</span></th>
                                <th width="15%">Standar</th>
                                <th width="20%">Aktual / Hasil</th>
                                <th width="15%">Status <span class="text-danger">*</span></th>
                                <th width="15%">Catatan</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tasksTableBody">
                            <tr class="default-row">
                                <td>
                                    {{-- Menggunakan new_details karena ini dianggap input baru --}}
                                    <input type="text" name="new_details[default_1][task_description]" 
                                        class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <input type="text" name="new_details[default_1][standard_value]" 
                                        class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input type="text" name="new_details[default_1][actual_value]" 
                                        class="form-control form-control-sm">
                                </td>
                                <td>
                                    <select name="new_details[default_1][pm_status]" class="form-control form-control-sm" required>
                                        <option value="">Pilih</option>
                                        <option value="OK">OK</option>
                                        <option value="Not OK">Not OK</option>
                                        <option value="Adjusted">Adjusted</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="new_details[default_1][pm_notes]" 
                                        class="form-control form-control-sm">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-row"><i class="ri-close-fill"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BAGIAN C: KESIMPULAN --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">Kesimpulan Akhir</div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="overall_result">Hasil Keseluruhan PM: <span class="text-danger">*</span></label>
                    <select name="overall_result" class="form-control" required>
                        <option value="">Pilih Hasil</option>
                        <option value="Baik" {{ old('overall_result', $schedule->overall_result) == 'Baik' ? 'selected' : '' }}>Baik (Normal)</option>
                        <option value="Perbaikan Minor" {{ old('overall_result', $schedule->overall_result) == 'Perbaikan Minor' ? 'selected' : '' }}>Perbaikan Minor (Selesai)</option>
                        <option value="Tindak Lanjut" {{ old('overall_result', $schedule->overall_result) == 'Tindak Lanjut' ? 'selected' : '' }}>Perlu Tindak Lanjut (Kerusakan Ditemukan)</option>
                    </select>
                    @error('overall_result')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group mb-3">
                    <label for="notes">Catatan Umum Teknisi:</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $schedule->notes) }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg btn-block">💾 Simpan & Selesaikan PM</button>
        <a href="{{ route('pm.index') }}" class="btn btn-secondary btn-block mt-2">Batal / Kembali</a>
    </form>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tasksTableBody = document.getElementById('tasksTableBody');
        const addRowBtn = document.getElementById('addRowBtn');
        let newRowCounter = 0; 

        // Template untuk baris baru (menggunakan new_details)
        const newRowTemplate = `
            <tr class="new-row">
                <td>
                    <input type="text" name="new_details[NEW_ID][task_description]" 
                        class="form-control form-control-sm" required>
                </td>
                <td>
                    <input type="text" name="new_details[NEW_ID][standard_value]" 
                        class="form-control form-control-sm">
                </td>
                <td>
                    <input type="text" name="new_details[NEW_ID][actual_value]" 
                        class="form-control form-control-sm">
                </td>
                <td>
                    <select name="new_details[NEW_ID][pm_status]" class="form-control form-control-sm" required>
                        <option value="">Pilih</option>
                        <option value="OK">OK</option>
                        <option value="Not OK">Not OK</option>
                        <option value="Adjusted">Adjusted</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="new_details[NEW_ID][pm_notes]" 
                        class="form-control form-control-sm">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row"><i class="ri-close-fill"></i></button>
                </td>
            </tr>
        `;

        // Fungsi untuk menambah baris baru
        addRowBtn.addEventListener('click', function (e) {
            // ✅ PERBAIKAN: Mencegah tindakan default tombol (agar tidak submit form)
            e.preventDefault(); 
            
            // Gunakan timestamp untuk memastikan ID unik
            const uniqueId = Date.now() + '_' + newRowCounter; 
            const newRow = newRowTemplate.replace(/NEW_ID/g, uniqueId);
            tasksTableBody.insertAdjacentHTML('beforeend', newRow);
            newRowCounter++;
        });

        // Event listener untuk menghapus baris (menggunakan event delegation)
        tasksTableBody.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
                // ✅ PERBAIKAN: Mencegah tindakan default tombol hapus
                e.preventDefault(); 

                const button = e.target.closest('.remove-row');
                // Pastikan setidaknya ada satu baris tersisa
                if (tasksTableBody.getElementsByTagName('tr').length > 1) {
                    button.closest('tr').remove();
                } else {
                    alert('Minimal harus ada satu baris tugas.');
                }
            }
        });
    });
</script>
@endsection