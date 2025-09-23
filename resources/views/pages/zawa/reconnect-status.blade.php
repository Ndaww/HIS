@extends('layouts.app')
@section('title','PKS - Verifikasi PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Reconnect Zawa Session</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Zawa</span> / <span>Reconnect</span></div>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($status === 'success')
                <h4 class="card-title text-success">✅ Reconnection Successful</h4>
                <p>{{ $message }}</p>
                <a href="{{ url('/zawa/status') }}" class="btn btn-primary mt-3">Back to Status</a>
            @else
                <h4 class="card-title text-danger">❌ Reconnection Failed</h4>
                <p>{{ $message }}</p>
                <a href="{{ url('/zawa/create-session') }}" class="btn btn-warning mt-3">Create New Session</a>
            @endif
        </div>
    </div>
@endsection

@section('js')
<script>
    // Add your JavaScript here
</script>
@endsection
