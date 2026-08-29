<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Login - eFasting System</title>

  <meta name="theme-color" content="#0f4c81">

  <!-- Google Fonts & Font Awesome -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Core Design System -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 50% 10%, #1e40af 0%, #0f172a 100%); padding: 24px;">
  
  <div style="width: 100%; max-width: 440px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px); border-radius: var(--radius-xl); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); padding: 40px 36px; border: 1px solid rgba(255, 255, 255, 0.3);">
    
    <!-- Top Branding Logo & Title -->
    <div style="text-align: center; margin-bottom: 28px;">
      <div style="width: 52px; height: 52px; background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: white; box-shadow: 0 8px 16px rgba(15, 76, 129, 0.25); margin-bottom: 14px;">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
      <h1 style="font-size: 22px; font-weight: 800; color: var(--primary-800); letter-spacing: -0.5px; margin-bottom: 4px;">eFasting System</h1>
      <p style="font-size: 12.5px; color: var(--slate-500); font-weight: 500;">Silakan Login</p>
    </div>

    <!-- Alert Notifications -->
    @if ($errors->any())
      <div style="background: var(--danger-light); color: var(--danger-600); padding: 12px 14px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 600; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    @if (session('success'))
      <div style="background: var(--success-light); color: var(--success-600); padding: 12px 14px; border-radius: var(--radius-md); font-size: 12.5px; font-weight: 600; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
      </div>
    @endif

    <!-- Login Form -->
    <form action="{{ route('login') }}" method="POST" id="formLogin">
      @csrf

      <div class="form-group-modern">
        <label for="username" class="form-label-modern">Username Akun</label>
        <div class="input-container">
          <i class="fa-solid fa-user input-icon-left"></i>
          <input type="text" name="username" id="username" class="form-control-modern" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus autocomplete="username">
        </div>
      </div>

      <div class="form-group-modern">
        <label for="password" class="form-label-modern">Password</label>
        <div class="input-container">
          <i class="fa-solid fa-lock input-icon-left"></i>
          <input type="password" name="password" id="password" class="form-control-modern" placeholder="Masukkan password" required autocomplete="current-password" style="padding-right: 42px;">
          <i class="fa-solid fa-eye" id="eyeIcon" onclick="togglePassword()" style="position: absolute; right: 14px; color: var(--slate-400); cursor: pointer; font-size: 15px;"></i>
        </div>
      </div>

      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; font-size: 12.5px;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--slate-600); user-select: none;">
          <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="accent-color: var(--primary-600); width: 15px; height: 15px;">
          <span>Ingat saya di perangkat ini</span>
        </label>
      </div>

      <button type="submit" id="btnLogin" class="btn-enterprise btn-enterprise-primary" style="width: 100%; padding: 13px; font-size: 14px;">
        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk ke Dashboard
      </button>
    </form>

    <!-- Default Accounts Hint -->
    <div style="margin-top: 24px; padding: 12px; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); font-size: 11.5px; color: var(--slate-500); line-height: 1.5;">
      <span style="font-weight: 700; color: var(--primary-700);"><i class="fa-solid fa-key"></i> Kredensial Default:</span>
      <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;">
        <span>Admin: <code>admin</code> / <code>admin123</code></span> &bull;
        <span>Internal: <code>petugas_internal</code> / <code>petugas123</code></span>
      </div>
    </div>

    <div style="text-align: center; margin-top: 24px; font-size: 11px; color: var(--slate-400);">
      Developed by Asset NIC Palembang &bull; 2026
    </div>

  </div>

  <script>
    function togglePassword() {
      const p = document.getElementById('password');
      const i = document.getElementById('eyeIcon');
      if (p.type === 'password') {
        p.type = 'text';
        i.classList.replace('fa-eye', 'fa-eye-slash');
        i.style.color = 'var(--primary-600)';
      } else {
        p.type = 'password';
        i.classList.replace('fa-eye-slash', 'fa-eye');
        i.style.color = 'var(--slate-400)';
      }
    }
  </script>
</body>

</html>
