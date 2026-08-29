<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Login - eFasting System</title>

  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="eFasting Login">
  <meta name="theme-color" content="#004b87">

  <!-- Google Fonts & Font Awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- CSS Terpisah -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
  <div class="app-container">
    <div id="viewLogin" class="view-section active">
      <div class="header" style="padding: 35px 20px;">
        <h2>eFasting system</h2>
        <h2>(Electronics Fixed Asset Stock Taking System)</h2>
        <p>Asset Management System developed by Asset NIC Palembang</p>
      </div>

      <div class="form-content">
        <div style="text-align: center; margin-bottom: 25px;">
          <h3 style="color: var(--main-blue); font-size: 18px; margin-bottom: 5px;">Silakan Login</h3>
          <p style="color: var(--text-muted); font-size: 13px;">Gunakan kredensial yang terdaftar di sistem</p>
        </div>

        @if ($errors->any())
          <div class="alert-box alert-danger" style="display: block; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-xmark"></i> {{ $errors->first() }}
          </div>
        @endif

        @if (session('success'))
          <div class="alert-box alert-success" style="display: block; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('login') }}" method="POST" id="formLogin">
          @csrf

          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-user icon-left"></i>
              <input type="text" name="username" id="username" class="form-control"
                placeholder="Masukkan username" value="{{ old('username') }}" required autofocus autocomplete="username">
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <i class="fa-solid fa-lock icon-left"></i>
              <input type="password" name="password" id="password" class="form-control"
                placeholder="Masukkan password" required autocomplete="current-password">
              <i class="fa-solid fa-eye icon-right" id="eyeIcon" onclick="togglePassword()"></i>
            </div>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 13px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-muted); margin: 0;">
              <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
              <span>Ingat saya di perangkat ini</span>
            </label>
          </div>

          <button type="submit" id="btnLogin" class="btn-primary btn-yellow">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
          </button>
        </form>

        <div style="margin-top: 25px; text-align: center; font-size: 11px; color: var(--text-muted);">
          eFasting Enterprise Edition &bull; Laravel 11
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const p = document.getElementById('password');
      const i = document.getElementById('eyeIcon');
      if (p.type === 'password') {
        p.type = 'text';
        i.classList.replace('fa-eye', 'fa-eye-slash');
        i.style.color = 'var(--main-blue)';
      } else {
        p.type = 'password';
        i.classList.replace('fa-eye-slash', 'fa-eye');
        i.style.color = 'var(--text-muted)';
      }
    }
  </script>
</body>

</html>
