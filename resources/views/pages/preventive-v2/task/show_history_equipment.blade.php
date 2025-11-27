@extends('layouts.app')

@section('title', 'History PM Item: ' . $equipments->name)

@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">History PM Item: {{ $equipments->name }}</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive V2</span> / Riwayat PM / Detail / Equipment / {{ $equipments->name }} </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">History Pelaksanaan</h6>
        </div>
        <div class="card-body">

                <table class="table table-bordered border-1">
                    <thead class="bg-secondary text-center">
                        <th width="5px">No</th>
                        <th>Teknisi</th>
                        <th>Tanggal PM</th>
                        <th>Hasil</th>
                        <th>Catatan</th>
                    </thead>
                    <tbody>
                    @if ($histories->isnotEmpty())
                        @foreach ($histories as $history)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $history->technician->name }} </td>
                                <td> {{ \Carbon\Carbon::parse($history->pm_date)->format('d M Y') }} </td>
                                <td> {{ $history->overall_result }} </td>
                                <td> {{ $history->notes }} </td>
                            </tr>
                        @endforeach
                    @else
                    <tr>
                        <td class="text-center h3" colspan="5">--- Belum Ada Tindakan Preventive ---</td>
                    </tr>
                    @endif
                    </tbody>
                </table>

            {{-- <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Equipment:</label>
                    <p class="form-control-static">{{ $equipments->name ?? '-' }} (ID: {{ $histories->equipment_id }})</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Teknisi Pelaksana:</label>
                    <p class="form-control-static">{{ $histories->technician->name ?? '-' }} (ID: {{ $histories->technician_id }})</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="font-weight-bold">Tgl. Pelaksanaan:</label>
                    <p class="form-control-static">{{ \Carbon\Carbon::parse($histories->pm_date)->format('d M Y') }}</p>
                </div>
            </div> --}}

            <hr>


        </div>
    </div>

    <a href="{{ route('pm.history') }}" class="btn btn-secondary mt-2">← Kembali ke Riwayat PM</a>

@endsection
