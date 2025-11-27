<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Rumah Sakit</title>
  <link rel="stylesheet" href="{{ asset('/assets/bootstrap-5.0.2/css/bootstrap.min.css') }}">
  <style>
    body {
      background-color: #e9f7ef;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      max-width: 960px;
      width: 100%;
      display: flex;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 128, 0, 0.1);
      background-color: white;
    }

    .login-left {
      flex: 1;
      background-color: #d0f0c0;
      padding: 3rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
    }

    .login-left h2 {
      font-weight: 700;
      margin-bottom: 1rem;
      color: #155724;
    }

    .login-left p {
      color: #155724;
      font-size: 16px;
    }

    .login-left img {
      max-width: 100%;
      height: auto;
      margin: 2rem 0;
    }

    .login-left .brand {
      font-weight: bold;
      color: #155724;
    }

    .login-right {
      flex: 1;
      padding: 3rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .form-label {
      font-weight: 500;
    }

    .btn-success {
      background-color: #28a745;
      border: none;
    }

    .btn-success:hover {
      background-color: #218838;
    }

    @media (max-width: 768px) {
      .login-card {
        flex-direction: column;
      }

      .login-left,
      .login-right {
        padding: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-card">
    <!-- KIRI -->
    <div class="login-left">
      <h2>Selamat Datang Kembali!</h2>
      <p>
        Terima kasih telah menjadi bagian dari pelayanan kami.<br />
        Silakan login untuk melanjutkan aktivitas Anda.
      </p>
      <img src="{{ asset('/assets/img/welcome.svg') }}" alt="Gambar Selamat Datang" width="100%">

    </div>

    <!-- KANAN -->
    <div class="login-right">
      <div class="text-center mb-4">
        <h3 class="fw-bold">Login Page</h3>
        <p class="text-muted">Masukkan NIK dan kata sandi Anda</p>
      </div>
      <form method="POST" action="/login">
        @csrf
        <div class="mb-3">
          <label for="nik" class="form-label">NIK</label>
          <input type="text" class="form-control" id="nik" name="nik" placeholder="Masukkan NIK" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Kata Sandi</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required />
        </div>
        <button type="submit" class="btn btn-success w-100">Masuk</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('/assets/bootstrap-5.0.2/js/bootstrap.min.js') }}"></script>
</body>
</html>
