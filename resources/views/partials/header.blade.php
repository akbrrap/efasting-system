@props(['title' => 'Dashboard'])

@php
  $user = auth()->user();
  $role = strtoupper($user->jenis_user ?? 'GUEST');
  $nama = $user->nama_karyawan ?? $user->username ?? 'User';
@endphp

<header class="topbar">
  <div class="topbar-left">
    <button type="button" class="hamburger-btn" onclick="toggleSidebar(true)" aria-label="Buka Menu">
      <i class="fa-solid fa-bars-staggered"></i>
    </button>

    <div class="page-breadcrumb">
      <span class="breadcrumb-label">eFasting System</span>
      <h1 class="page-title-main">{{ $title }}</h1>
    </div>
  </div>

  <div class="topbar-right">
    <div class="system-status-pill">
      <div class="status-dot"></div>
      <span>Database Aktif</span>
    </div>

    <!-- User Profile Chip -->
    <div style="display: flex; align-items: center; gap: 10px; background: var(--slate-100); padding: 5px 12px 5px 6px; border-radius: 30px; border: 1px solid var(--slate-200);">
      <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
        {{ strtoupper(substr($nama, 0, 1)) }}
      </div>
      <div style="display: flex; flex-direction: column; text-align: left;">
        <span style="font-weight: 700; font-size: 12px; color: var(--slate-800); line-height: 1.1;">{{ $nama }}</span>
        <span style="font-size: 10px; font-weight: 700; color: var(--primary-600);">{{ $role }}</span>
      </div>
    </div>
  </div>
</header>
