@if($qr)
    <h2>Scan QR WhatsApp</h2>
<img src="data:image/png;base64,{{ $qr }}" alt="QR Code">

@else
    <p>QR tidak tersedia</p>
@endif