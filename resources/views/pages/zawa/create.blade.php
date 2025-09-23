@extends('layouts.app')
@section('title','PKS - Verifikasi PKS')
@section('main-content')
    <div class="header-breadcrumb">
        <h2 id="page-title">Buat Sesi Zawa</h2>
        <div class="breadcrumb" id="breadcrumb"><span>Zawa</span> / <span>Buat Sesi</span></div>
    </div>

    <div class="card">
        <div class="card-body text-center">
            <h4 class="card-title">Pindai QR Code untuk Terhubung</h4>
            <p>Silakan pindai QR code di bawah ini menggunakan aplikasi Zawa Anda untuk menghubungkan sesi baru.</p>
            @if (isset($qr))
                <img src="data:image/png;base64,{{ $qr }}" alt="QR Code" class="img-fluid my-4" style="max-width: 250px;">
                <p>Setelah dipindai, sesi Anda akan aktif dan tersimpan di file **.env**.</p>
            @else
                <div class="alert alert-danger" role="alert">
                    Gagal membuat QR Code. Silakan cek koneksi Anda dan coba lagi.
                </div>
                <a href="{{ url('/zawa/create-session') }}" class="btn btn-warning mt-3">Muat Ulang Halaman</a>
            @endif
        </div>
    </div>
@endsection

@section('js')
<script>
    // Tambahkan JavaScript jika diperlukan di sini
</script>
@endsection
