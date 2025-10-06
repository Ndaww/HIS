@extends('layouts.app')
@section('title', isset($realization) ? 'Edit Realisasi Tugas PM' : 'Eksekusi Tugas PM')

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">{{ isset($realization) ? 'Edit Realisasi Tugas' : 'Mulai Tugas PM' }}</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / {{ isset($realization) ? 'Edit' : 'Eksekusi' }}</div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8 offset-md-2">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Formulir {{ isset($realization) ? 'Edit' : 'Penyelesaian' }} Tugas</h5>
                </div>
                <div class="card-body">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Tentukan Aksi dan Method Form Dinamis --}}
                    <form action="{{ isset($realization) ? route('pm_shift.update') : route('pm_shift.store') }}" method="POST">
                        @csrf
                        
                        <h4>Detail Tugas</h4>
                        <table class="table table-bordered table-sm mb-4">
                            <tr>
                                <th>Equipment</th>
                                <td>{{ $task->equipmentType->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Tugas</th>
                                <td>{{ $task->task_name }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $task->task_category }}</td>
                            </tr>
                            <tr>
                                <th>Periode Target</th>
                                <td>Bulan {{ $month }}/{{ $year }} (Shift: {{ $shift }})</td>
                            </tr>
                        </table>
                        
                        {{-- Hidden Inputs --}}
                        <input type="hidden" name="master_pm_task_id" value="{{ $task->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="assigned_shift" value="{{ $shift }}">
                        
                        {{-- Field Tanggal Penyelesaian (Hanya muncul saat Edit) --}}
                        @if (isset($realization))
                        <div class="form-group mb-3">
                            <label for="completion_date">Tanggal dan Waktu Selesai</label>
                            @php
                                // Format tanggal agar kompatibel dengan input datetime-local
                                $compDate = old('completion_date', $realization->completion_date ? \Carbon\Carbon::parse($realization->completion_date)->format('Y-m-d\TH:i') : \Carbon\Carbon::now()->format('Y-m-d\TH:i'));
                            @endphp
                            <input type="datetime-local" name="completion_date" id="completion_date" class="form-control" value="{{ $compDate }}" required>
                        </div>
                        @else
                            {{-- Untuk Store, kita tidak butuh input tanggal, tanggal akan diisi Carbon::now() --}}
                            <input type="hidden" name="completion_date" value=""> 
                        @endif

                        <div class="form-group mb-4">
                            <label for="notes">Catatan Teknis (Opsional)</label>
                            @php
                                // Ambil nilai dari $realization->notes jika ada, jika tidak, pakai old()
                                $noteValue = old('notes', isset($realization) ? $realization->notes : '');
                            @endphp
                            <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Masukkan catatan atau temuan penting selama pelaksanaan tugas.">{{ $noteValue }}</textarea>
                        </div>

                        <div class="alert alert-info">
                            @if (isset($realization))
                                Anda sedang mengedit data realisasi tugas ini.
                            @else
                                Dengan menekan tombol "Selesaikan Tugas", Anda mengonfirmasi bahwa tugas ini telah selesai dilaksanakan pada waktu saat ini.
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pm_shift.index', ['month' => $month, 'year' => $year, 'shift' => $shift]) }}" class="btn btn-secondary">
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-{{ isset($realization) ? 'warning' : 'success' }}">
                                {{ isset($realization) ? 'Update Perubahan' : 'Selesaikan Tugas Sekarang' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection