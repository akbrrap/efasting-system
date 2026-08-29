@php
  $user = auth()->user();
  $role = strtoupper($user->jenis_user ?? 'GUEST');
  $currentRoute = request()->route() ? request()->route()->getName() : '';
  $nama = $user->nama_karyawan ?? $user->username ?? 'User';
@endphp

<aside class="sidebar-wrapper" id="sidebarWrapper">
  <!-- Brand Header -->
  <div class="sidebar-brand">
    <div class="brand-icon">
      <i class="fa-solid fa-boxes-stacked"></i>
    </div>
    <div class="brand-info">
      <h2>eFasting Pro</h2>
      <span>Asset Management</span>
    </div>
    <!-- Mobile Close Button -->
    <button type="button" class="btn-enterprise-outline" style="display: none; margin-left: auto; width: 32px; height: 32px; padding: 0;" onclick="toggleSidebar(false)">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <!-- Navigation Links -->
  <nav class="sidebar-nav">
    <div class="nav-section-title">Menu Utama</div>

    <!-- 1. Home Dashboard -->
    <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
      <i class="fa-solid fa-chart-pie nav-icon"></i>
      <span>Executive Dashboard</span>
    </a>

    <!-- 2. Fixed Assets Stock Opname (Internal & External) -->
    @if ($role === 'ADMINISTRATOR' || $role === 'INTERNAL' || $role === 'EKSTERNAL')
      <div class="nav-section-title">Operasional Opname</div>
      
      <div class="nav-group {{ str_starts_with($currentRoute, 'opname.') ? 'open' : '' }}" id="groupOpname">
        <div class="nav-group-header" onclick="toggleSubmenu('groupOpname', this)">
          <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-clipboard-check nav-icon"></i>
            <span>Stock Opname Fisik</span>
          </div>
          <i class="fa-solid fa-chevron-down nav-chevron"></i>
        </div>

        <ul class="nav-sublist">
          @if ($role === 'ADMINISTRATOR' || $role === 'INTERNAL')
            <li>
              <a href="{{ route('opname.internal') }}" class="nav-subitem {{ $currentRoute === 'opname.internal' ? 'active' : '' }}">
                <i class="fa-solid fa-industry" style="font-size: 11px;"></i>
                <span>Opname Internal (Pabrik)</span>
              </a>
            </li>
          @endif

          @if ($role === 'ADMINISTRATOR' || $role === 'EKSTERNAL')
            <li>
              <a href="{{ route('opname.external') }}" class="nav-subitem {{ $currentRoute === 'opname.external' ? 'active' : '' }}">
                <i class="fa-solid fa-truck-ramp-box" style="font-size: 11px;"></i>
                <span>Opname Eksternal (Vendor)</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
    @endif

    <!-- 3. Master Assets Database (Khusus Administrator) -->
    @if ($role === 'ADMINISTRATOR')
      <div class="nav-section-title">Kelola Master Asset</div>

      <div class="nav-group {{ str_starts_with($currentRoute, 'asset.') ? 'open' : '' }}" id="groupMaster">
        <div class="nav-group-header" onclick="toggleSubmenu('groupMaster', this)">
          <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-server nav-icon"></i>
            <span>Master Database</span>
          </div>
          <i class="fa-solid fa-chevron-down nav-chevron"></i>
        </div>

        <ul class="nav-sublist">
          <li>
            <a href="{{ route('asset.index') }}" class="nav-subitem {{ $currentRoute === 'asset.index' ? 'active' : '' }}">
              <i class="fa-solid fa-table-list" style="font-size: 11px;"></i>
              <span>Daftar Master Aset</span>
            </a>
          </li>
          <li>
            <a href="{{ route('asset.create') }}" class="nav-subitem {{ $currentRoute === 'asset.create' ? 'active' : '' }}">
              <i class="fa-solid fa-file-circle-plus" style="font-size: 11px;"></i>
              <span>Mass Asset Addition</span>
            </a>
          </li>
          <li>
            <a href="{{ route('asset.adjustment') }}" class="nav-subitem {{ $currentRoute === 'asset.adjustment' ? 'active' : '' }}">
              <i class="fa-solid fa-sliders" style="font-size: 11px;"></i>
              <span>Mass Adjustment</span>
            </a>
          </li>
          <li>
            <a href="{{ route('asset.retirement') }}" class="nav-subitem {{ $currentRoute === 'asset.retirement' ? 'active' : '' }}">
              <i class="fa-solid fa-trash-can" style="font-size: 11px;"></i>
              <span>Mass Retirement</span>
            </a>
          </li>
        </ul>
      </div>
    @endif

    <!-- 4. Laporan & Audit Trail (Administrator & Internal) -->
    @if ($role === 'ADMINISTRATOR' || $role === 'INTERNAL')
      <div class="nav-section-title">Audit & Pelaporan</div>

      <a href="{{ route('opname.audit_trail') }}" class="nav-item {{ $currentRoute === 'opname.audit_trail' ? 'active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
        <span>Fixed Asset Audit Trail</span>
      </a>

      <a href="{{ route('reports.index') }}" class="nav-item {{ $currentRoute === 'reports.index' ? 'active' : '' }}">
        <i class="fa-solid fa-file-excel nav-icon"></i>
        <span>Download Laporan Excel</span>
      </a>
    @endif
  </nav>

  <!-- Sidebar User Profile Footer -->
  <div class="sidebar-user">
    <div class="user-meta">
      <div class="user-avatar">
        {{ strtoupper(substr($nama, 0, 1)) }}
      </div>
      <div class="user-details">
        <div class="user-name" title="{{ $nama }}">{{ $nama }}</div>
        <div class="user-badge">{{ $role }}</div>
      </div>
    </div>

    <!-- Quick Logout Button -->
    <form method="POST" action="{{ route('logout') }}" id="logoutFormSidebar" style="margin: 0;">
      @csrf
      <button type="submit" class="btn-sidebar-logout" title="Keluar / Logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
      </button>
    </form>
  </div>
</aside>
