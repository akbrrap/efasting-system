@extends('layouts.app')

@section('title', 'Reports & Export')

@section('content')
<div id="viewReports" class="view-section active">
  @include('partials.header', ['title' => 'Reports & Export'])

  <div class="form-content">
    <div style="background: var(--main-blue-light); padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: var(--main-blue);">
      <i class="fa-solid fa-file-excel"></i> <strong>Laporan Audit Stock Opname</strong>
      <p style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
        Ekspor data hasil opname internal maupun eksternal ke format spreadsheet Microsoft Excel / CSV.
      </p>
    </div>

    <form action="{{ route('reports.export') }}" method="GET" id="formExport">
      <div class="form-group">
        <label>Kategori Laporan <span style="color:red">*</span></label>
        <select id="exportKategori" name="kategori" class="form-control" required>
          <option value="INTERNAL" selected>🏭 Opname Internal Assets</option>
          <option value="EXTERNAL">🚚 Opname External Assets</option>
        </select>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Tanggal Awal</label>
          <input type="date" id="exportStart" name="start_date" class="form-control">
        </div>
        <div class="form-group">
          <label>Tanggal Akhir</label>
          <input type="date" id="exportEnd" name="end_date" class="form-control">
        </div>
      </div>

      <div class="alert-box alert-info" style="display:block; margin-bottom:20px; font-size:11px; line-height:1.4;">
        <i class="fa-solid fa-circle-info"></i> <strong>Tips:</strong> Kosongkan kedua tanggal untuk menarik <strong>seluruh data</strong> yang ada di database.
      </div>

      <button type="submit" id="btnExport" class="btn-primary" style="background:#27ae60;">
        <i class="fa-solid fa-file-excel"></i> Unduh File Laporan Excel (.xlsx)
      </button>
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
      return showModal('error', 'Format Tanggal', 'Harap isi KEDUA tanggal, atau KOSONGKAN keduanya untuk menarik semua data.');
    }

    if (start && end && new Date(start) > new Date(end)) {
      e.preventDefault();
      return showModal('error', 'Tanggal Invalid', 'Tanggal awal tidak boleh melewati tanggal akhir.');
    }
  });
</script>
@endpush
