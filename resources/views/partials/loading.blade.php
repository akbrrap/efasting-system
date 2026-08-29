<div class="modal-overlay" id="loadingOverlay" style="z-index: 9999; backdrop-filter: blur(8px);">
  <div style="background: #ffffff; padding: 24px 36px; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); display: flex; flex-direction: column; align-items: center; gap: 14px; border: 1px solid var(--border-card);">
    <div style="width: 44px; height: 44px; border: 4px solid var(--primary-100); border-top-color: var(--primary-600); border-radius: 50%; animation: spinLoader 0.8s linear infinite;"></div>
    <span style="font-weight: 700; font-size: 13.5px; color: var(--primary-800); letter-spacing: 0.2px;" id="loadingText">Memproses Data...</span>
  </div>
</div>

<style>
@keyframes spinLoader {
  to { transform: rotate(360deg); }
}
</style>
