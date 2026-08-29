@extends('layouts.app')

@section('title', 'Reports & Export')

@section('content')
<div style="max-width: 840px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-file-zipper" style="color: var(--primary-600);"></i> Ekspor Laporan Hasil Audit & Dokumentasi Foto
        </h2>
        <p class="card-subtitle-text">Unduh hasil sensus aset ke format arsip ZIP (Excel .xlsx + folder foto fisik & tagging terpisah)</p>
      </div>
      <span class="badge-pill badge-primary">
        <i class="fa-solid fa-cloud-arrow-down"></i> ZIP / Excel Export
      </span>
    </div>

    <form action="{{ route('reports.export') }}" method="GET" id="formExport">
      <div class="form-group-modern">
        <label for="exportKategori" class="form-label-modern">Kategori Laporan yang Diekspor <span class="req">*</span></label>
        <select id="exportKategori" name="kategori" class="form-control-modern" required>
          <option value="INTERNAL" selected>🏭 Hasil Opname Internal Assets (Pabrik & Kantor)</option>
          <option value="EXTERNAL">🚚 Hasil Opname External Assets (Agen & Distributor Channel INN dan Reguler)</option>
        </select>
      </div>

      <div class="form-group-modern">
        <label for="exportFormat" class="form-label-modern">Format Unduhan <span class="req">*</span></label>
        <select id="exportFormat" name="format" class="form-control-modern" required>
          <option value="zip" selected>📦 Paket ZIP Lengkap (Excel .xlsx + Folder Foto Fisik & Tagging Terpisah)</option>
          <option value="xlsx">📊 Hanya File Microsoft Excel (.xlsx)</option>
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label for="exportStart" class="form-label-modern">Filter Tanggal Awal Sensus</label>
          <div class="input-container">
            <i class="fa-solid fa-calendar-day input-icon-left"></i>
            <input type="date" id="exportStart" name="start_date" class="form-control-modern">
          </div>
        </div>

        <div class="form-group-modern">
          <label for="exportEnd" class="form-label-modern">Filter Tanggal Akhir Sensus</label>
          <div class="input-container">
            <i class="fa-solid fa-calendar-check input-icon-left"></i>
            <input type="date" id="exportEnd" name="end_date" class="form-control-modern">
          </div>
        </div>
      </div>

      <div style="background: var(--primary-50); border: 1px solid rgba(15, 76, 129, 0.15); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
        <i class="fa-solid fa-folder-tree" style="color: var(--primary-600); font-size: 18px; margin-top: 2px;"></i>
        <div style="font-size: 12.5px; color: var(--slate-700); line-height: 1.6;">
          <strong style="color: var(--primary-800);">Struktur Arsip Unduhan ZIP:</strong><br>
          <span style="font-family: monospace; font-size: 12px; color: var(--primary-700); display: block; margin-top: 4px; background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 6px; border: 1px dashed var(--primary-200);">
            📁 Laporan_Opname_XXXX.zip<br>
            ├── 📊 Laporan_Opname_XXXX.xlsx<br>
            ├── 📁 foto_fisik/ (Kumpulan foto kondisi fisik aset)<br>
            └── 📁 foto_tagging/ (Kumpulan foto tagging barcode/label aset)
          </span>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end;">
        <button type="submit" id="btnExport" class="btn-enterprise btn-enterprise-primary" style="min-width: 240px; padding: 13px;">
          <i class="fa-solid fa-download"></i> Unduh Laporan Sekarang
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.getElementById('formExport').addEventListener('submit', function (e) {
    const start = document.getElementById('exportStart').value;
    const end = document.getElementById('exportEnd').value;

    if ((start && !end) || (!start && end)) {
      e.preventDefault();
      return showModal('error', 'Format Tanggal Kurang Lengkap', 'Harap isi KEDUA tanggal (Awal & Akhir), atau KOSONGKAN keduanya untuk mengekspor semua data.');
    }

    if (start && end && new Date(start) > new Date(end)) {
      e.preventDefault();
      return showModal('error', 'Rentang Tanggal Invalid', 'Tanggal awal sensus tidak boleh lebih besar dari tanggal akhir.');
    }
  });
</script>
@endpush
