<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') - eFasting Enterprise</title>

  <!-- PWA & Mobile Meta Tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#0f4c81">

  <!-- Typography & Font Awesome Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Core Enterprise Design System -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('styles')
</head>

<body>
  <!-- Loading Spinner Overlay -->
  @include('partials.loading')

  <!-- Modal Dialogs System -->
  @include('partials.modals')

  <div class="app-layout">
    <!-- Sidebar Drawer Backdrop for Mobile -->
    <div class="drawer-backdrop" id="drawerBackdrop" onclick="toggleSidebar(false)"></div>

    <!-- 1. Left Sidebar Navigation -->
    @include('partials.sidebar')

    <!-- 2. Main Wrapper (Topbar + Workspace) -->
    <div class="main-wrapper">
      @include('partials.header', ['title' => trim($__env->yieldContent('title', 'eFasting Enterprise'))])

      <main class="content-workspace">
        @yield('content')
      </main>
    </div>
  </div>

  <!-- Global UI & Helper JavaScript -->
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Sidebar & Drawer Toggle (Rock-solid for mobile & desktop)
    function toggleSidebar(forceState = null) {
      const sidebar = document.getElementById('sidebarWrapper');
      const backdrop = document.getElementById('drawerBackdrop');

      if (!sidebar) return;

      const shouldOpen = (forceState !== null) ? forceState : !sidebar.classList.contains('open');

      if (shouldOpen) {
        sidebar.classList.add('open');
        if (backdrop) backdrop.style.display = 'block';
        document.body.style.overflow = (window.innerWidth <= 1024) ? 'hidden' : '';
      } else {
        sidebar.classList.remove('open');
        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
      }
    }

    // Submenu Collapsible Accordion
    function toggleSubmenu(groupId, element) {
      const group = document.getElementById(groupId);
      if (!group) return;

      const isOpen = group.classList.contains('open');
      
      // Close other open submenus for clean accordion look
      document.querySelectorAll('.nav-group.open').forEach(el => {
        if (el !== group) el.classList.remove('open');
      });

      if (isOpen) {
        group.classList.remove('open');
      } else {
        group.classList.add('open');
      }
    }

    // Loading Indicator
    function showLoading(show = true) {
      const el = document.getElementById('loadingOverlay');
      if (el) el.style.display = show ? 'flex' : 'none';
    }

    // Custom Glass Modal System
    let isModalSuccess = false;
    let modalCallback = null;

    function showModal(type, title, desc, align = 'center', callback = null) {
      isModalSuccess = (type === 'success');
      modalCallback = callback;

      const iconBox = document.getElementById('modalIconWrapper');
      if (iconBox) {
        iconBox.className = `modal-icon-wrapper ${type}`;
        iconBox.innerHTML = (type === 'success') 
          ? '<i class="fa-solid fa-check"></i>' 
          : (type === 'error') 
            ? '<i class="fa-solid fa-xmark"></i>' 
            : '<i class="fa-solid fa-info"></i>';
      }

      const titleEl = document.getElementById('modalTitle');
      if (titleEl) titleEl.innerText = title;

      const descEl = document.getElementById('modalDesc');
      if (descEl) {
        descEl.innerHTML = desc;
        descEl.style.textAlign = align;
      }

      const btn = document.getElementById('modalBtn');
      if (btn) {
        btn.className = (type === 'success' || type === 'info') 
          ? 'btn-enterprise btn-enterprise-primary' 
          : 'btn-enterprise btn-enterprise-danger';
        btn.innerText = (type === 'success' || type === 'info') ? 'Mengerti' : 'Tutup & Perbaiki';
      }

      const modal = document.getElementById('customModal');
      if (modal) modal.style.display = 'flex';
    }

    function closeModal() {
      const modal = document.getElementById('customModal');
      if (modal) modal.style.display = 'none';
      if (typeof modalCallback === 'function') {
        modalCallback();
        modalCallback = null;
      }
    }

    function tutupConfirmModal() {
      const confirmModal = document.getElementById('confirmModal');
      if (confirmModal) confirmModal.style.display = 'none';
    }

    function bukaPreviewFoto(url) {
      if (!url || url === 'undefined' || url.trim() === '') {
        return showModal('error', 'Tidak Ada Foto', 'Aset ini belum dilengkapi dengan foto dokumentasi.');
      }
      const img = document.getElementById('previewImageModal');
      const cleanUrl = url.trim();

      if (img) {
        img.src = cleanUrl;
        img.style.display = 'block';
      }

      const photoModal = document.getElementById('photoModal');
      if (photoModal) photoModal.style.display = 'flex';
    }

    function tutupPreviewFoto() {
      const photoModal = document.getElementById('photoModal');
      if (photoModal) photoModal.style.display = 'none';
      const img = document.getElementById('previewImageModal');
      if (img) img.src = '';
    }

    function formatRibuan(angka) {
      if (angka === null || angka === undefined || angka === '') return '-';
      let bersih = angka.toString().replace(/,/g, '').replace(/\./g, '');
      let num = Number(bersih);
      return isNaN(num) ? angka.toString() : num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatLiveRupiah(input) {
      let value = input.value.replace(/[^0-9-]/g, '');
      if (!value) { input.value = ''; return; }
      let isNegative = value.startsWith('-');
      value = value.replace(/-/g, '');
      input.value = (isNegative ? '-' : '') + value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Auto close drawer when window resized to desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth > 1024) {
        const backdrop = document.getElementById('drawerBackdrop');
        if (backdrop) backdrop.style.display = 'none';
        document.body.style.overflow = '';
      }
    });
  </script>

  @stack('scripts')
</body>

</html>
