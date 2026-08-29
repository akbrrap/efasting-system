@php
  $user = auth()->user();
  $role = strtoupper($user->jenis_user ?? '');
  $currentRoute = request()->route()->getName();
@endphp

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h3 style="font-size: 16px; margin:0;"><i class="fa-solid fa-bars-progress"></i> Menu Opname</h3>
    <i class="fa-solid fa-xmark" style="font-size: 20px; cursor:pointer;" onclick="toggleSidebar()"></i>
  </div>

  <ul class="sidebar-menu">
    <!-- 1. Home Dashboard -->
    <li id="menuHome" class="{{ $currentRoute === 'dashboard' ? 'active-menu' : '' }}">
      <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
        <i class="fa-solid fa-house" style="width: 20px; color:var(--text-muted); margin-right: 8px;"></i> Home Dashboard
      </a>
    </li>

    <!-- 2. Fixed Assets Stock Opname Submenu -->
    @if ($role === 'ADMINISTRATOR' || $role === 'INTERNAL' || $role === 'EKSTERNAL')
      <li class="menu-category {{ str_starts_with($currentRoute, 'opname.') ? 'expanded' : '' }}" id="catOpname" onclick="toggleSubmenu('subOpname', this)">
        <div class="category-title">
          <span><i class="fa-solid fa-boxes-packing" style="width: 20px;"></i> Fixed Assets Stock Opname</span>
          <i class="fa-solid fa-chevron-down chevron-icon"></i>
        </div>
      </li>
      <ul class="submenu-container {{ str_starts_with($currentRoute, 'opname.') ? 'open' : '' }}" id="subOpname">
        @if ($role === 'ADMINISTRATOR' || $role === 'INTERNAL')
          <li id="menuInternal" class="{{ $currentRoute === 'opname.internal' ? 'active-menu' : '' }}">
            <a href="{{ route('opname.internal') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
              <i class="fa-solid fa-industry" style="margin-right: 8px;"></i> Stock opname Internal Assets
            </a>
          </li>
        @endif

        @if ($role === 'ADMINISTRATOR' || $role === 'EKSTERNAL')
          <li id="menuExternal" class="{{ $currentRoute === 'opname.external' ? 'active-menu' : '' }}">
            <a href="{{ route('opname.external') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
              <i class="fa-solid fa-truck-fast" style="margin-right: 8px;"></i> Stock Opname External Assets
            </a>
          </li>
        @endif
      </ul>
    @endif

    <!-- 3. Master Assets Database Submenu (Khusus Administrator) -->
    @if ($role === 'ADMINISTRATOR')
      <li class="menu-category {{ str_starts_with($currentRoute, 'asset.') ? 'expanded' : '' }}" id="catMaster" onclick="toggleSubmenu('subMaster', this)">
        <div class="category-title">
          <span><i class="fa-solid fa-database" style="width: 20px;"></i> Master Assets Database</span>
          <i class="fa-solid fa-chevron-down chevron-icon"></i>
        </div>
      </li>
      <ul class="submenu-container {{ str_starts_with($currentRoute, 'asset.') ? 'open' : '' }}" id="subMaster">
        <li id="menuMasterAsset" class="{{ $currentRoute === 'asset.index' ? 'active-menu' : '' }}">
          <a href="{{ route('asset.index') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
            <i class="fa-solid fa-list" style="margin-right: 8px;"></i> Fixed Asset List
          </a>
        </li>
        <li id="menuAddAsset" class="{{ $currentRoute === 'asset.create' ? 'active-menu' : '' }}">
          <a href="{{ route('asset.create') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
            <i class="fa-solid fa-folder-plus" style="margin-right: 8px;"></i> Fixed Asset Addition
          </a>
        </li>
        <li id="menuAdjustment" class="{{ $currentRoute === 'asset.adjustment' ? 'active-menu' : '' }}">
          <a href="{{ route('asset.adjustment') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
            <i class="fa-solid fa-sliders" style="margin-right: 8px;"></i> Asset Adjustment
          </a>
        </li>
        <li id="menuRetirement" class="{{ $currentRoute === 'asset.retirement' ? 'active-menu' : '' }}">
          <a href="{{ route('asset.retirement') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
            <i class="fa-solid fa-trash-can" style="margin-right: 8px;"></i> Fixed Asset Retirements
          </a>
        </li>
      </ul>

      <!-- 4. Fixed Asset Audit Trail -->
      <li id="menuRiwayat" class="{{ $currentRoute === 'audit.index' ? 'active-menu' : '' }}">
        <a href="{{ route('audit.index') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
          <i class="fa-solid fa-clock-rotate-left" style="width: 20px; color:var(--text-muted); margin-right: 8px;"></i> Fixed Asset Audit Trail
        </a>
      </li>

      <!-- 5. Reports -->
      <li id="menuReports" class="{{ $currentRoute === 'reports.index' ? 'active-menu' : '' }}">
        <a href="{{ route('reports.index') }}" style="display:flex; align-items:center; color:inherit; text-decoration:none; width:100%;">
          <i class="fa-solid fa-file-excel" style="width: 20px; color:var(--text-muted); margin-right: 8px;"></i> Reports
        </a>
      </li>
    @endif
  </ul>

  <div class="sidebar-footer">
    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
      Login sebagai: <br>
      <strong style="color:var(--main-blue);">{{ $user->nama_karyawan ?? 'Petugas' }} ({{ $role }})</strong>
    </div>

    <form action="{{ route('logout') }}" method="POST" id="sidebarLogoutForm">
      @csrf
      <button type="submit" class="btn-primary" style="padding: 10px; font-size: 13px; background: #e74c3c; width:100%;">
        <i class="fa-solid fa-power-off"></i> Logout
      </button>
    </form>
  </div>
</div>
