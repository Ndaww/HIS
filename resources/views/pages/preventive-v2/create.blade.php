@extends('layouts.app')
@section('title','Penentuan Target Bulanan Preventive Spesialis')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Penentuan Target Bulanan</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Preventive</span> / Target Bulanan</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('preventive-target.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="equipment_id" class="form-label">Equipment</label>
                    <select name="equipment_id" id="equipment_id" class="form-select" required>
                        <option value="">-- Pilih Equipment --</option>
                        @foreach($equipments as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select" required>
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <input type="number" name="tahun" id="tahun" class="form-control"
                           value="{{ date('Y') }}" required>
                </div>

                <div class="mb-3">
                    <label for="target_bulanan" class="form-label">Target Preventive (kali)</label>
                    <input type="number" name="target_bulanan" id="target_bulanan" class="form-control"
                           placeholder="contoh: 4" required>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Target</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script>
    // bisa ditambah SweetAlert konfirmasi kalau mau
</script>
@endsection
