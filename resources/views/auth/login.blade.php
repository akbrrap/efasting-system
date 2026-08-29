<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Login - eFasting Enterprise Asset System</title>

  <meta name="theme-color" content="#0f4c81">

  <!-- Google Fonts & Font Awesome -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- CSS Terpisah -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 50% 10%, #1e40af 0%, #0f172a 100%); padding: 20px;">
  
  <div style="width: 100%; max-width: 1000px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(20px); border-radius: var(--radius-xl); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); display: grid; grid-template-columns: 1fr 1.1fr; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.2);">
    
    <!-- Left Showcase Branding -->
    <div style="background: linear-gradient(145deg, var(--primary-800) 0%, var(--primary-900) 100%); padding: 48px 40px; display: flex; flex-direction: column; justify-content: space-between; color: white; position: relative;">
      <div style="position: absolute; top: -50px; left: -50px; width: 180px; height: 180px; background: rgba(59, 130, 246, 0.15); border-radius: 50%; filter: blur(40px);"></div>
      <div style="position: absolute; bottom: -50px; right: -50px; width: 180px; height: 180px; background: rgba(245, 158, 11, 0.15); border-radius: 50%; filter: blur(40px);"></div>

      <div style="position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
          <div style="width: 44px; height: 44px; background: linear-gradient(135deg, var(--accent-500) 0%, var(--accent-600) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
            <i class="fa-solid fa-boxes-stacked"></i>
          </div>
          <div>
            <h1 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.1;">eFasting System</h1>
            <span style="font-size: 11px; font-weight: 600; color: var(--accent-500); text-transform: uppercase; letter-spacing: 0.5px;">Fixed Asset Stock Taking</span>
          </div>
        </div>

        <p style="font-size: 13.5px; color: var(--primary-200); line-height: 1.6; margin-bottom: 30px;">
          Platform Enterprise Terintegrasi untuk Audit, Tracking, dan Verifikasi Fisik Aset Perusahaan secara Real-time.
        </p>

        <!-- Feature Points -->
        <div style="display: flex; flex-direction: column; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-500); font-size: 12px;">
              <i class="fa-solid fa-camera"></i>
            </div>
            <span style="font-size: 12.5px; color: var(--slate-100); font-weight: 500;">Dokumentasi Foto Fisik & Tagging Otomatis</span>
          </div>
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-500); font-size: 12px;">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <span style="font-size: 12.5px; color: var(--slate-100); font-weight: 500;">Otorisasi Role (Admin, Internal, Eksternal)</span>
          </div>
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-500); font-size: 12px;">
              <i class="fa-solid fa-file-excel"></i>
            </div>
            <span style="font-size: 12.5px; color: var(--slate-100); font-weight: 500;">Mass Import Excel & Realtime Variance Analytics</span>
          </div>
        </div>
      </div>

      <div style="position: relative; z-index: 1; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 11px; color: var(--slate-400);">
        Developed by Asset NIC Palembang &bull; Enterprise 2026
      </div>
    </div>

    <!-- Right Login Form -->
    <div style="padding: 48px 40px; display: flex; flex-direction: column; justify-content: center;">
      <div style="margin-bottom: 28px;">
        <h2 style="font-size: 22px; font-weight: 800; color: var(--slate-900); letter-spacing: -0.5px;">Silakan Login</h2>
        <p style="font-size: 13px; color: var(--slate-500); margin-top: 4px;">Gunakan kredensial akun Anda untuk mengakses sistem</p>
      </div>

      @if ($errors->any())
        <div style="background: var(--danger-light); color: var(--danger-600); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      @if (session('success'))
        <div style="background: var(--success-light); color: var(--success-600); padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-circle-check"></i>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      <form action="{{ route('login') }}" method="POST" id="formLogin">
        @csrf

        <div class="form-group-modern">
          <label for="username" class="form-label-modern">Username Akun</label>
          <div class="input-container">
            <i class="fa-solid fa-user input-icon-left"></i>
            <input type="text" name="username" id="username" class="form-control-modern" placeholder="Masukkan username Anda" value="{{ old('username') }}" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="form-group-modern">
          <label for="password" class="form-label-modern">Password</label>
          <div class="input-container">
            <i class="fa-solid fa-lock input-icon-left"></i>
            <input type="password" name="password" id="password" class="form-control-modern" placeholder="Masukkan password Anda" required autocomplete="current-password" style="padding-right: 42px;">
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
    </div>
  </div>

  <style>
    @media (max-width: 768px) {
      body > div {
        grid-template-columns: 1fr !important;
      }
      body > div > div:first-child {
        display: none !important;
      }
    }
  </style>

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
