@extends('layouts.app') 

@section('title', 'Detail PM Form: ' . $header->id)

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Detail Pelaksanaan PM - ID: {{ $header->id }}</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive V2</span> / Riwayat PM / Detail</div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- BAGIAN A: INFORMASI DASAR & WAKTU --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">Informasi Pelaksanaan & Hasil Kesimpulan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Equipment:</label>
                    <p class="form-control-static">{{ $header->equipment->name ?? '-' }} (ID: {{ $header->equipment_id }})</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Teknisi Pelaksana:</label>
                    <p class="form-control-static">{{ $header->technician->name ?? '-' }} (ID: {{ $header->technician_id }})</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Tgl. Pelaksanaan:</label>
                    <p class="form-control-static">{{ \Carbon\Carbon::parse($header->pm_date)->format('d M Y') }}</p>
                </div>
            </div>
            
            <hr>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Waktu Mulai:</label>
                    <p class="form-control-static">{{ $header->start_time }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Waktu Selesai:</label>
                    <p class="form-control-static">{{ $header->end_time }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Total Durasi:</label>
                    {{-- ASUMSI Anda menghitung dan menyediakan durasi di Controller atau Model --}}
                    <p class="form-control-static">
                        @php
                            $start = \Carbon\Carbon::parse($header->start_time);
                            $end = \Carbon\Carbon::parse($header->end_time);
                            $diff = $start->diff($end);
                            $duration = '';
                            if ($diff->h > 0) { $duration .= $diff->h . ' jam '; }
                            if ($diff->i > 0) { $duration .= $diff->i . ' menit'; }
                            echo trim($duration) ?: '-';
                        @endphp
                    </p>
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="font-weight-bold">Hasil Keseluruhan PM:</label>
                @php
                    $badgeClass = match ($header->overall_result) {
                        'Baik' => 'bg-success',
                        'Perbaikan Minor' => 'bg-warning text-dark',
                        'Tindak Lanjut' => 'bg-danger',
                        default => 'bg-secondary',
                    };
                @endphp
                <p class="form-control-static">
                    <span class="badge {{ $badgeClass }} p-2">{{ $header->overall_result }}</span>
                </p>
            </div>
            
            <div class="form-group">
                <label class="font-weight-bold">Catatan Umum Teknisi:</label>
                <textarea class="form-control" rows="3" readonly>{{ $header->notes ?? 'Tidak ada catatan.' }}</textarea>
            </div>
        </div>
    </div>

    {{-- BAGIAN B: DETAIL TUGAS/CHECKLIST --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning text-dark">
            <h6 class="m-0 font-weight-bold">Detail Checklist Tugas PM</h6>
        </div>
        <div class="card-body">
            @if ($header->details->isEmpty())
                <p class="text-center text-muted">Tidak ada detail tugas yang tersimpan untuk formulir ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No.</th>
                                <th width="35%">Deskripsi Tugas</th>
                                <th width="15%">Standar</th>
                                <th width="15%">Aktual / Hasil</th>
                                <th width="15%">Status</th>
                                <th width="15%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($header->details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->task_description }}</td>
                                <td>{{ $detail->standard_value ?? '-' }}</td>
                                <td>{{ $detail->actual_value ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusBadge = match ($detail->pm_status) {
                                            'OK' => 'bg-success',
                                            'Not OK' => 'bg-danger',
                                            'Adjusted' => 'bg-info',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusBadge }}">{{ $detail->pm_status }}</span>
                                </td>
                                <td>{{ $detail->pm_notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('pm.history') }}" class="btn btn-secondary mt-2">← Kembali ke Riwayat PM</a>

@endsection
