<!-- 1. Custom Glass Notification Modal -->
<div class="modal-overlay" id="customModal">
  <div class="modal-box-glass">
    <div class="modal-icon-wrapper success" id="modalIconWrapper">
      <i class="fa-solid fa-check"></i>
    </div>
    <h3 class="modal-title-text" id="modalTitle">Pemberitahuan</h3>
    <div class="modal-body-text" id="modalDesc">
      Pesan notifikasi sistem.
    </div>
    <div style="display: flex; justify-content: center; gap: 10px;">
      <button type="button" class="btn-enterprise btn-enterprise-primary" id="modalBtn" onclick="closeModal()" style="min-width: 140px;">
        Mengerti
      </button>
    </div>
  </div>
</div>

<!-- 2. Custom Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box-glass">
    <div class="modal-icon-wrapper error" style="background: var(--warning-light); color: var(--warning-500);">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <h3 class="modal-title-text" id="confirmTitle">Konfirmasi Tindakan</h3>
    <div class="modal-body-text" id="confirmDesc">
      Apakah Anda yakin ingin melanjutkan proses ini?
    </div>
    <div style="display: flex; justify-content: center; gap: 12px;">
      <button type="button" class="btn-enterprise btn-enterprise-outline" onclick="tutupConfirmModal()" style="min-width: 100px;">
        Batal
      </button>
      <button type="button" class="btn-enterprise btn-enterprise-primary" id="confirmBtnAction" style="min-width: 120px;">
        Ya, Lanjutkan
      </button>
    </div>
  </div>
</div>

<!-- 3. Photo & Document High-Resolution Preview Modal -->
<div class="modal-overlay" id="photoModal">
  <div class="modal-box-glass" style="max-width: 640px; padding: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--slate-100); padding-bottom: 10px;">
      <h3 style="font-size: 15px; font-weight: 700; color: var(--primary-800); display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-image" style="color: var(--primary-600);"></i> Dokumentasi Foto Opname
      </h3>
      <button type="button" class="btn-enterprise-outline" style="width: 32px; height: 32px; padding: 0; border-radius: 50%;" onclick="tutupPreviewFoto()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="border-radius: var(--radius-md); overflow: hidden; background: var(--slate-900); display: flex; align-items: center; justify-content: center; min-height: 320px; max-height: 500px;">
      <img id="previewImageModal" alt="Preview Dokumentasi Opname" style="max-width: 100%; max-height: 480px; object-fit: contain; display: block;">
    </div>

    <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
      <button type="button" class="btn-enterprise btn-enterprise-outline" onclick="tutupPreviewFoto()">
        Tutup Preview
      </button>
    </div>
  </div>
</div>
