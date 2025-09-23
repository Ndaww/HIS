@extends('layouts.app')
@section('title','PKS - Verifikasi PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Whatsapp Checker</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Zawa </span> / Check Status</div>
    </div>

    <div class="card">
        <div class="card-body">
        @if($status['connected'] == 'success')
            <h4 class="card-title text-success">✅ Notifikasi Berhasil Dikirim</h4>
            <p>Pesan uji coba berhasil dikirim. Berikut respons dari API:</p>
            <pre><code>{{ json_encode($data, JSON_PRETTY_PRINT) }}</code></pre>
            <a href="{{ url('/zawa/check-status') }}" class="btn btn-primary mt-3">Kembali ke Status</a>
        @else

        @endif
        </div>
    </div>
@endsection

@section('js')
<script>
    // Tambahkan JavaScript jika diperlukan di sini
</script>
@endsection
