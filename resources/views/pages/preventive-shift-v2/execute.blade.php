@extends('layouts.app')
@section('title', 'Eksekusi Ronde Shift ' . $round->shift_name)

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Eksekusi Ronde PM</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Ronde PM / Eksekusi</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body">
                    <h5 class="card-title">Ronde Shift **{{ $round->shift_name }}**</h5>
                    <p class="card-text">Teknisi: {{ $round->technician->name }} | Mulai: {{ $round->start_time }}</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('pm_rounds.save_results', $round->id) }}" id="executionForm">
                @csrf

                @forelse ($equipments as $equipment)
                    @php
                        $equipmentTypeId = $equipment->equipment_type_id;
                        $tasks = $masterTasks[$equipmentTypeId] ?? collect();
                    @endphp
                    
                    <div class="card shadow mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                {{ $equipment->name }} 
                                <span class="badge bg-secondary">{{ $equipment->equipmentType->name ?? 'N/A' }}</span>
                            </h6>
                            <small class="text-muted">{{ $equipment->location }}</small>
                        </div>
                        <div class="card-body">
                            
                            @if($tasks->isEmpty())
                                <p class="text-danger">❌ Tidak ada Master Tugas terdefinisi untuk tipe equipment ini.</p>
                            @endif

                            @foreach ($tasks as $task)
                                <div class="row mb-3 border-bottom pb-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold">{{ $task->task_name }}</label>
                                        <span class="badge {{ match ($task->task_category) {'I' => 'bg-info', 'L' => 'bg-success', 'C' => 'bg-warning', 'T' => 'bg-danger', default => 'bg-secondary', } }} text-white">{{ $task->task_category }}</span>
                                        <p class="small text-muted mt-1">Standar (Threshold): *{{ $task->anomaly_threshold }}*</p>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        {{-- Input untuk Hasil Pengecekan --}}
                                        <input type="hidden" name="results[{{ $equipment->id }}][{{ $task->id }}][master_pm_task_id]" value="{{ $task->id }}">
                                        <input type="hidden" name="results[{{ $equipment->id }}][{{ $task->id }}][equipment_id]" value="{{ $equipment->id }}">

                                        {{-- Dropdown Hasil --}}
                                        <div class="form-group mb-2">
                                            <select name="results[{{ $equipment->id }}][{{ $task->id }}][check_result]" class="form-control check-result" data-task-id="{{ $task->id }}" data-equipment-id="{{ $equipment->id }}" required>
                                                <option value="">-- Pilih Hasil Cek --</option>
                                                <option value="Normal">✅ Normal (Sesuai Standar)</option>
                                                <option value="Anomaly">🚨 Anomali (Ada Temuan)</option>
                                            </select>
                                        </div>
                                        
                                        {{-- Input Deskripsi Anomali (Tersembunyi Awalnya) --}}
                                        <div class="form-group anomaly-group-{{ $equipment->id }}-{{ $task->id }}" style="display: none;">
                                            <label for="anomaly_desc_{{ $equipment->id }}_{{ $task->id }}" class="text-danger">Deskripsi Anomali <span class="text-danger">*</span></label>
                                            <textarea name="results[{{ $equipment->id }}][{{ $task->id }}][anomaly_description]" id="anomaly_desc_{{ $equipment->id }}_{{ $task->id }}" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="alert alert-danger">Tidak ada Equipment aktif yang ditemukan untuk Ronde ini.</div>
                @endforelse

                <div class="text-center mt-5 mb-5">
                    <button type="submit" class="btn btn-success btn-lg">
                        Simpan Hasil Cek (Draft)
                    </button>
                    <button type="button" id="completeRoundBtn" class="btn btn-danger btn-lg">
                        Selesaikan Ronde & Hitung Anomali
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Logika untuk menampilkan/menyembunyikan Deskripsi Anomali
        $('.check-result').change(function() {
            let result = $(this).val();
            let taskId = $(this).data('task-id');
            let equipmentId = $(this).data('equipment-id');
            let anomalyGroup = $(`.anomaly-group-${equipmentId}-${taskId}`);
            let anomalyDesc = $(`#anomaly_desc_${equipmentId}_${taskId}`);

            if (result === 'Anomaly') {
                anomalyGroup.slideDown();
                anomalyDesc.prop('required', true);
            } else {
                anomalyGroup.slideUp();
                anomalyDesc.prop('required', false);
            }
        });

        // ------------------------------------------
        // Logika Submit: Simpan Hasil (Draft)
        // ------------------------------------------
        $('#executionForm').submit(function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Simpan Hasil Sementara?',
                text: "Anda dapat melanjutkannya nanti. Status ronde tetap 'In Progress'.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    // Lanjutkan dengan AJAX submission ke route pm_rounds.save_results
                    this.submit(); // Di sini kita gunakan submit biasa untuk sementara, atau bisa diubah ke AJAX
                }
            });
        });

        // ------------------------------------------
        // Logika Selesaikan Ronde (Button)
        // ------------------------------------------
        $('#completeRoundBtn').click(function() {
            // Logika untuk validasi semua form terisi sebelum menyelesaikan
            if ($('select[required]').filter(function() { return !$(this).val(); }).length > 0) {
                 Swal.fire('Perhatian!', 'Semua hasil cek harus diisi sebelum menyelesaikan ronde.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Selesaikan Ronde?',
                text: "Waktu selesai akan dicatat dan total anomali akan dihitung.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Merah
                confirmButtonText: 'Ya, Selesaikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, kita ubah action form dan submit
                    $('#executionForm').attr('action', '{{ route('pm_rounds.complete_round', $round->id) }}');
                    $('#executionForm').submit();
                }
            });
        });
    });
</script>
@endsection