<!-- Modal Pesan Kustom -->
<div class="modal-overlay" id="customModal" style="display: none;">
  <div class="modal-box center-text" id="modalBoxElement">
    <div id="modalIcon"></div>
    <h3 class="modal-title" id="modalTitle">Title</h3>
    <div class="modal-desc" id="modalDesc">Description goes here.</div>
    <button id="modalBtn" class="btn-modal" onclick="closeModal()">OK</button>
  </div>
</div>

<!-- Modal Konfirmasi Disposal / Aksi Kritis -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
  <div class="modal-box center-text">
    <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation warning"></i></div>
    <h3 class="modal-title" id="confirmTitle">Konfirmasi Disposal</h3>
    <div class="modal-desc" id="confirmDesc"
      style="text-align: left; background: #fdfbf7; padding:15px; border-radius:8px; border: 1px dashed var(--main-yellow);">
    </div>
    <div class="grid-2">
      <button class="btn-modal btn-modal-outline" onclick="tutupConfirmModal()">Batal</button>
      <button class="btn-modal error" id="btnConfirmAction">Ya, Lanjutkan</button>
    </div>
  </div>
</div>

<!-- Modal Preview Foto Dokumentasi -->
<div class="modal-overlay" id="photoModal" style="display: none;">
  <div class="modal-box"
    style="max-width:95%; padding:10px; position:relative; background:transparent; box-shadow:none;">
    <button onclick="tutupPreviewFoto()"
      style="position:absolute; top:-10px; right:-10px; background:#e74c3c; color:#fff; border:none; border-radius:50%; width:35px; height:35px; font-size:18px; cursor:pointer; z-index:3001; box-shadow:0 4px 6px rgba(0,0,0,0.2);">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div style="background:#fff; border-radius:12px; overflow:hidden; padding:5px; text-align:center;">
      <img id="previewImageModal" src="" alt="Preview Dokumentasi Foto"
        style="width:100%; max-height:75vh; object-fit:contain; border-radius:8px; background:#f4f7f9; display:none;">
      <iframe id="previewFrameModal" src=""
        style="width:100%; height:65vh; border:none; border-radius:8px; background:#f4f7f9; display:none;"></iframe>
    </div>
  </div>
</div>
