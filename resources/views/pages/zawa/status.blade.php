@extends('layouts.app')
@section('title','PKS - Verifikasi PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title"></h2>
        <div class="breadcrumb" id="breadcrumb"><span></span> / </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($status['connected'] == 'success')
                <h4 class="card-title text-success">✅ Status: Connected</h4>
                <p>Zawa sudah terhubung dan sesi tersimpan di file **.env**.</p>
                <p>ID: <code>{{ $status['zawa_id'] }}</code></p>
                <p>Session ID: <code>{{ $status['session_id'] }}</code></p>
                <div class="row">
                <a href="/zawa/qr/send" target="_blank">Tes Kirim Notifikasi</a>
            </div>
            @else
                <h4 class="card-title text-danger">❌ Status: Not Connected</h4>
                <p>Zawa belum terhubung. Silakan buat sesi baru untuk melanjutkan.</p>
                <a href="{{ url('/zawa/create-session') }}" class="btn btn-primary mt-3">Buat Sesi Baru</a>
            @endif
        </div>
    </div>

@endsection

@section('js')

</script>
@endsection
