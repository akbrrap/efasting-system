<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'eFasting System') - Asset Management</title>

  <!-- PWA & Mobile Meta Tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="eFasting SO">
  <meta name="theme-color" content="#004b87">

  <!-- Typography & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- CSS Terpisah -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('styles')
</head>

<body>
  <!-- Loading Overlay -->
  @include('partials.loading')

  <!-- Sidebar Navigasi -->
  @include('partials.sidebar')

  <!-- Modal Dialogs -->
  @include('partials.modals')

  <!-- Main Content Container -->
  <div class="app-container">
    @yield('content')
  </div>

  <!-- Global UI & Helper Scripts -->
  <script>
    // CSRF Setup untuk Fetch/AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleSidebar() {
      const sb = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      if (sb) sb.classList.toggle('open');
      if (overlay) overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
    }

    function toggleSubmenu(id, el) {
      const sub = document.getElementById(id);
      if (sub) sub.classList.toggle('open');
      if (el) el.classList.toggle('expanded');
    }

    function showLoading(show = true) {
      const el = document.getElementById('loadingOverlay');
      if (el) el.style.display = show ? 'flex' : 'none';
    }

    let isModalSuccess = false;
    let modalCallback = null;

    function showModal(type, title, desc, align = 'center', callback = null) {
      isModalSuccess = (type === 'success');
      modalCallback = callback;

      let iconHTML = type === 'success' 
        ? '<i class="fa-solid fa-circle-check success"></i>' 
        : type === 'error' 
          ? '<i class="fa-solid fa-circle-xmark error"></i>' 
          : '<i class="fa-solid fa-circle-info" style="color:var(--main-blue);"></i>';

      let btnClass = type === 'success' || type === 'info' ? 'btn-modal success' : 'btn-modal error';

      const modalIcon = document.getElementById('modalIcon');
      if (modalIcon) modalIcon.innerHTML = `<div class="modal-icon">${iconHTML}</div>`;
      
      const modalTitle = document.getElementById('modalTitle');
      if (modalTitle) modalTitle.innerText = title;

      const modalDesc = document.getElementById('modalDesc');
      if (modalDesc) modalDesc.innerHTML = desc;

      const btn = document.getElementById('modalBtn');
      if (btn) {
        btn.className = btnClass;
        btn.innerText = (type === 'success' || type === 'info') ? 'Tutup' : 'Tutup & Perbaiki';
      }

      const modalBox = document.getElementById('modalBoxElement');
      if (modalBox) {
        align === 'left' ? modalBox.classList.remove('center-text') : modalBox.classList.add('center-text');
      }

      const customModal = document.getElementById('customModal');
      if (customModal) customModal.style.display = 'flex';
    }

    function closeModal() {
      const customModal = document.getElementById('customModal');
      if (customModal) customModal.style.display = 'none';
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
        return showModal('error', 'Tidak Ada Foto', 'Aset ini belum dilengkapi dengan dokumentasi foto.');
      }
      const img = document.getElementById('previewImageModal');
      const frame = document.getElementById('previewFrameModal');
      
      const cleanUrl = url.trim();
      
      // Deteksi jika link adalah gambar langsung vs dokumen iframe
      if (cleanUrl.match(/\.(jpeg|jpg|gif|png|webp)($|\?)/i) || cleanUrl.startsWith('data:image') || cleanUrl.includes('/storage/opname-photos/')) {
        img.src = cleanUrl;
        img.style.display = 'block';
        frame.style.display = 'none';
      } else {
        frame.src = cleanUrl;
        frame.style.display = 'block';
        img.style.display = 'none';
      }

      document.getElementById('photoModal').style.display = 'flex';
    }

    function tutupPreviewFoto() {
      document.getElementById('photoModal').style.display = 'none';
      document.getElementById('previewImageModal').src = '';
      document.getElementById('previewFrameModal').src = '';
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
  </script>

  @stack('scripts')
</body>

</html>
