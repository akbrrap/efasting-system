@extends('layouts.app')

@section('title', 'Mass Asset Addition')

@section('content')
<div style="max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <!-- 1. Mass Addition Excel Section -->
  <div class="card-panel" style="background: linear-gradient(135deg, rgba(239, 246, 255, 0.8) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1.5px solid var(--primary-200);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div style="max-width: 580px;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--success-light); color: var(--success-600); padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; margin-bottom: 8px;">
          <i class="fa-solid fa-file-excel"></i> Batch Upload
        </div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--primary-800); margin-bottom: 4px;">
          Import Masal Master Aset via Excel (.xlsx)
        </h3>
        <p style="font-size: 13px; color: var(--slate-600); line-height: 1.5;">
          Gunakan template Microsoft Excel resmi untuk mengunggah ratusan master aset sekaligus secara aman dan tervalidasi di backend server.
        </p>
      </div>

      <div style="display: flex; gap: 10px;">
        <a href="{{ route('asset.template', 'addition') }}" class="btn-enterprise btn-enterprise-outline" style="background: #ffffff;">
          <i class="fa-solid fa-download" style="color: var(--success-600);"></i> Unduh Template Excel
        </a>

        <label class="btn-enterprise btn-enterprise-primary" style="cursor: pointer; margin: 0;">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload File .xlsx
          <input type="file" id="fileMassAdd" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassAdditionUpload(event)">
        </label>
      </div>
    </div>
  </div>

  <!-- 2. Manual Single Asset Form -->
  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-plus-circle" style="color: var(--primary-600);"></i> Registrasi Aset Baru (Manual Entry)
        </h2>
        <p class="card-subtitle-text">Isi rincian informasi aset tetap untuk mendaftarkannya ke dalam master database</p>
      </div>
    </div>

    <form id="formAddAsset" action="{{ route('asset.store') }}" method="POST">
      @csrf

      <div class="form-group-modern">
        <label for="addKategori" class="form-label-modern">Kategori Database Aset <span class="req">*</span></label>
        <select id="addKategori" name="kategori_db" class="form-control-modern" required>
          <option value="" disabled selected>Pilih Kategori Database...</option>
          <option value="INTERNAL">🏭 Internal Database (Pabrik & Kantor Perusahaan)</option>
          <option value="EXTERNAL">🚚 External Database (Vendor & Gudang Distributor)</option>
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label for="addNo" class="form-label-modern">Nomor Aset Register <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-hashtag input-icon-left"></i>
            <input type="text" id="addNo" name="nomor_asset" class="form-control-modern" placeholder="Contoh: 10001007" required>
          </div>
        </div>

        <div class="form-group-modern">
          <label for="addSn" class="form-label-modern">Serial Number (SN) <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-barcode input-icon-left"></i>
            <input type="text" id="addSn" name="serial_number" class="form-control-modern" placeholder="Ketik strip '-' jika tidak ada SN" required>
          </div>
        </div>
      </div>

      <div class="form-group-modern">
        <label for="addDesc" class="form-label-modern">Deskripsi Lengkap Aset <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-box-open input-icon-left"></i>
          <input type="text" id="addDesc" name="deskripsi_asset" class="form-control-modern" placeholder="Nama, tipe, dan spesifikasi aset..." required>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label for="addCc" class="form-label-modern">Cost Center <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-building input-icon-left"></i>
            <input type="text" id="addCc" name="cost_center" class="form-control-modern" placeholder="Contoh: CC-LOG-01" required>
          </div>
        </div>

        <div class="form-group-modern">
          <label for="addQty" class="form-label-modern">Kuantitas Buku (Qty) <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-cubes input-icon-left"></i>
            <input type="number" id="addQty" name="qty_buku" class="form-control-modern" placeholder="Jumlah unit..." min="1" required>
          </div>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label for="addCap" class="form-label-modern">Capitalized Date (Cap Date) <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-calendar input-icon-left"></i>
            <input type="date" id="addCap" name="cap_date" class="form-control-modern" required>
          </div>
        </div>

        <div class="form-group-modern">
          <label for="addAlloc" class="form-label-modern">Alokasi Wilayah / Lokasi <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-location-dot input-icon-left"></i>
            <input type="text" id="addAlloc" name="allocation" class="form-control-modern" placeholder="Contoh: Palembang, Warehouse A" required>
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
        <div class="form-group-modern">
          <label for="addNilai" class="form-label-modern">Nilai Perolehan (Rp)</label>
          <div class="input-container">
            <i class="fa-solid fa-rupiah-sign input-icon-left"></i>
            <input type="text" id="addNilai" class="form-control-modern" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBV();">
            <input type="hidden" id="rawNilai" name="nilai_perolehan" value="0">
          </div>
        </div>

        <div class="form-group-modern">
          <label for="addDepresiasi" class="form-label-modern">Akumulasi Depresiasi (Rp)</label>
          <div class="input-container">
            <i class="fa-solid fa-chart-line-down input-icon-left"></i>
            <input type="text" id="addDepresiasi" class="form-control-modern" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBV();">
            <input type="hidden" id="rawDepresiasi" name="akumulasi_depresiasi" value="0">
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Net Book Value / NBV (Rp)</label>
          <div class="input-container">
            <i class="fa-solid fa-wallet input-icon-left" style="color: var(--success-600);"></i>
            <input type="text" id="addNbv" class="form-control-modern" readonly placeholder="0" style="font-weight: 700; color: var(--success-600); background: var(--success-light);">
            <input type="hidden" id="rawNbv" name="nbv" value="0">
          </div>
        </div>
      </div>

      <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('asset.index') }}" class="btn-enterprise btn-enterprise-outline">
          Batal & Kembali
        </a>
        <button type="submit" id="btnSubmitAdd" class="btn-enterprise btn-enterprise-primary" style="min-width: 180px;">
          <i class="fa-solid fa-save"></i> Simpan ke Database
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  function hitungNBV() {
    let nilai = Number(document.getElementById('addNilai').value.replace(/[^0-9-]/g, '')) || 0;
    let dep = Number(document.getElementById('addDepresiasi').value.replace(/[^0-9-]/g, '')) || 0;
    let nbv = nilai - dep;

    document.getElementById('rawNilai').value = nilai;
    document.getElementById('rawDepresiasi').value = dep;
    document.getElementById('rawNbv').value = nbv;
    document.getElementById('addNbv').value = (nbv < 0 ? '-' : '') + Math.abs(nbv).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  // Handle Form Single Asset Submission via AJAX
  document.getElementById('formAddAsset').addEventListener('submit', function (e) {
    e.preventDefault();
    hitungNBV();
    showLoading(true);

    const formData = new FormData(this);

    fetch("{{ route('asset.store') }}", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      },
      body: formData
    })
    .then(res => res.json())
    .then(res => {
      showLoading(false);
      if (res.status === 'success') {
        showModal('success', 'Aset Berhasil Didaftarkan', res.message, 'center', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        showModal('error', 'Gagal Menambah Aset', res.message || 'Terjadi kesalahan validasi.');
      }
    })
    .catch(err => {
      showLoading(false);
      console.error(err);
      showModal('error', 'Kesalahan Sistem', 'Tidak dapat terhubung ke server.');
    });
  });

  // Handle Mass Addition Upload
  function handleMassAdditionUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls', 'csv'].includes(ext)) {
      showModal('error', 'Format Tidak Didukung', 'Harap unggah file spreadsheet berekstensi .xlsx, .xls, atau .csv');
      event.target.value = '';
      return;
    }

    showLoading(true);
    const formData = new FormData();
    formData.append('file_excel', file);

    fetch("{{ route('asset.mass_addition') }}", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      },
      body: formData
    })
    .then(res => res.json())
    .then(res => {
      showLoading(false);
      event.target.value = '';

      if (res.status === 'success') {
        let msg = `<strong>${res.message}</strong><br><br>`;
        msg += `Total Baris: ${res.total_rows || 0}<br>`;
        msg += `Berhasil Dimasukkan: <strong style="color:var(--success-600);">${res.inserted || 0}</strong><br>`;
        if (res.skipped > 0) {
          msg += `Dilewati (Duplikat): <strong style="color:var(--warning-500);">${res.skipped}</strong><br>`;
        }
        if (res.errors && res.errors.length > 0) {
          msg += `<br><span style="color:var(--danger-500); font-weight:700;">Catatan Error:</span><br>${res.errors.slice(0, 5).join('<br>')}`;
        }
        showModal('success', 'Mass Addition Selesai', msg, 'left', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        showModal('error', 'Gagal Upload Mass Addition', res.message || 'Gagal memproses file.');
      }
    })
    .catch(err => {
      showLoading(false);
      event.target.value = '';
      console.error(err);
      showModal('error', 'Kesalahan Server', 'Gagal mengunggah file spreadsheet ke server.');
    });
  }
</script>
@endpush
