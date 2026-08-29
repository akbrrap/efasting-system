@extends('layouts.app')

@section('title', 'Reports & Export')

@section('content')
<div style="max-width: 840px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-file-excel" style="color: var(--success-600);"></i> Ekspor Laporan Hasil Audit Sensus
        </h2>
        <p class="card-subtitle-text">Unduh hasil opname fisik aset tetap (Internal & Eksternal) ke format spreadsheet Microsoft Excel (.xlsx) resmi</p>
      </div>
      <span class="badge-pill badge-success">
        <i class="fa-solid fa-file-export"></i> Excel Export
      </span>
    </div>

    <form action="{{ route('reports.export') }}" method="GET" id="formExport">
      <div class="form-group-modern">
        <label for="exportKategori" class="form-label-modern">Kategori Laporan yang Diekspor <span class="req">*</span></label>
        <select id="exportKategori" name="kategori" class="form-control-modern" required>
          <option value="INTERNAL" selected>🏭 Hasil Opname Internal Assets (Pabrik & Kantor)</option>
          <option value="EXTERNAL">🚚 Hasil Opname External Assets (Vendor & Distributor)</option>
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

      <div style="background: var(--info-light); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px;">
        <i class="fa-solid fa-circle-info" style="color: var(--info-500); font-size: 16px; margin-top: 2px;"></i>
        <div style="font-size: 12.5px; color: var(--slate-700); line-height: 1.5;">
          <strong>Informasi Filter Tanggal:</strong><br>
          Kosongkan kedua tanggal jika ingin mengekspor <strong>seluruh data historis opname</strong> yang tersimpan di sistem.
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end;">
        <button type="submit" id="btnExport" class="btn-enterprise btn-enterprise-success" style="min-width: 220px; padding: 13px;">
          <i class="fa-solid fa-download"></i> Unduh File Laporan (.xlsx)
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
