@extends('layouts.app')

@section('title', 'Mass Asset Adjustment')

@section('content')
<div style="max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <!-- 1. Mass Adjustment Excel Section -->
  <div class="card-panel" style="background: linear-gradient(135deg, rgba(254, 243, 199, 0.5) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1.5px solid var(--accent-light);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div style="max-width: 580px;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--accent-light); color: var(--accent-600); padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; margin-bottom: 8px;">
          <i class="fa-solid fa-file-pen"></i> Batch Value Adjustment
        </div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--primary-800); margin-bottom: 4px;">
          Penyesuaian Nilai Aset Masal via Excel (.xlsx)
        </h3>
        <p style="font-size: 13px; color: var(--slate-600); line-height: 1.5;">
          Unduh template spreadsheet berisi daftar seluruh master aset, ubah Nilai Perolehan (NP) atau Akumulasi Depresiasi (AD), lalu unggah kembali file Excel ke server.
        </p>
      </div>

      <div style="display: flex; gap: 10px;">
        <a href="{{ route('asset.template', 'adjustment') }}" class="btn-enterprise btn-enterprise-outline" style="background: #ffffff;">
          <i class="fa-solid fa-download" style="color: var(--accent-600);"></i> Unduh Template Excel
        </a>

        <label class="btn-enterprise btn-enterprise-yellow" style="cursor: pointer; margin: 0;">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload Hasil .xlsx
          <input type="file" id="fileMassAdj" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassAdjustmentUpload(event)">
        </label>
      </div>
    </div>
  </div>

  <!-- 2. Manual Adjustment Form -->
  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-sliders" style="color: var(--primary-600);"></i> Penyesuaian Nilai Perolehan Satuan
        </h2>
        <p class="card-subtitle-text">Cari aset yang ingin disesuaikan nilainya, perbarui nilai perolehan & akumulasi depresiasi</p>
      </div>
    </div>

    <form id="formAdjustment">
      @csrf

      <div class="form-group-modern">
        <label for="adjKategori" class="form-label-modern">Kategori Database <span class="req">*</span></label>
        <select id="adjKategori" name="kategori_db" class="form-control-modern" onchange="resetPencarianAdj()">
          <option value="INTERNAL" selected>🏭 Internal Database (Pabrik / Kantor)</option>
          <option value="EXTERNAL">🚚 External Database (Agen & Distributor Channel INN dan Reguler)</option>
        </select>
      </div>

      <!-- Pencarian Aset -->
      <div class="form-group-modern">
        <label for="searchAdjInput" class="form-label-modern">Cari Aset yang Akan Di-adjust <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
          <input type="text" id="searchAdjInput" class="form-control-modern" placeholder="Ketik No Aset / Nama / Serial Number..." autocomplete="off" onkeyup="cariAsetAdj()" onfocus="bukaDropdownAdj()">
          <i class="fa-solid fa-circle-xmark" id="clearSearchAdjBtn" style="display:none; position: absolute; right: 14px; color: var(--danger-500); font-size: 16px; cursor: pointer;" onclick="resetPencarianAdj()"></i>
        </div>
        <div id="dropdownAdj" class="search-dropdown-box"></div>
        <input type="hidden" id="adjAssetNo" name="nomor_asset">
      </div>

      <div class="form-group-modern">
        <label class="form-label-modern">Deskripsi Aset Terpilih</label>
        <div class="input-container">
          <i class="fa-solid fa-box-open input-icon-left"></i>
          <input type="text" id="adjDesc" class="form-control-modern" readonly placeholder="Otomatis terisi saat aset dipilih...">
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label class="form-label-modern">Serial Number (SN)</label>
          <div class="input-container">
            <i class="fa-solid fa-barcode input-icon-left"></i>
            <input type="text" id="adjSn" class="form-control-modern" readonly placeholder="-">
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Kuantitas Buku</label>
          <div class="input-container">
            <i class="fa-solid fa-book input-icon-left"></i>
            <input type="number" id="adjQty" class="form-control-modern" readonly placeholder="0">
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
        <div class="form-group-modern">
          <label for="adjNilai" class="form-label-modern">Nilai Perolehan Baru (Rp) <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-rupiah-sign input-icon-left"></i>
            <input type="text" id="adjNilai" class="form-control-modern" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBVAdj();">
            <input type="hidden" id="rawAdjNilai" value="0">
          </div>
        </div>

        <div class="form-group-modern">
          <label for="adjDepresiasi" class="form-label-modern">Akumulasi Depresiasi Baru (Rp) <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-chart-line-down input-icon-left"></i>
            <input type="text" id="adjDepresiasi" class="form-control-modern" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBVAdj();">
            <input type="hidden" id="rawAdjDepresiasi" value="0">
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Net Book Value Baru / NBV (Rp)</label>
          <div class="input-container">
            <i class="fa-solid fa-wallet input-icon-left" style="color: var(--success-600);"></i>
            <input type="text" id="adjNbv" class="form-control-modern" readonly placeholder="0" style="font-weight: 700; color: var(--success-600); background: var(--success-light);">
            <input type="hidden" id="rawAdjNbv" value="0">
          </div>
        </div>
      </div>

      <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('asset.index') }}" class="btn-enterprise btn-enterprise-outline">
          Batal & Kembali
        </a>
        <button type="button" id="btnAdjSubmit" class="btn-enterprise btn-enterprise-primary" onclick="submitAdjustment()" style="min-width: 180px;">
          <i class="fa-solid fa-check-double"></i> Simpan Penyesuaian
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  let searchTimer = null;

  function hitungNBVAdj() {
    let nilai = Number(document.getElementById('adjNilai').value.replace(/[^0-9-]/g, '')) || 0;
    let dep = Number(document.getElementById('adjDepresiasi').value.replace(/[^0-9-]/g, '')) || 0;
    let nbv = nilai - dep;

    document.getElementById('rawAdjNilai').value = nilai;
    document.getElementById('rawAdjDepresiasi').value = dep;
    document.getElementById('rawAdjNbv').value = nbv;
    document.getElementById('adjNbv').value = (nbv < 0 ? '-' : '') + Math.abs(nbv).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function cariAsetAdj() {
    clearTimeout(searchTimer);
    const query = document.getElementById('searchAdjInput').value.trim();
    const kategori = document.getElementById('adjKategori').value;
    const dropdown = document.getElementById('dropdownAdj');
    const clearBtn = document.getElementById('clearSearchAdjBtn');

    if (query.length > 0) {
      if (clearBtn) clearBtn.style.display = 'block';
    } else {
      if (clearBtn) clearBtn.style.display = 'none';
      if (dropdown) dropdown.style.display = 'none';
      return;
    }

    searchTimer = setTimeout(() => {
      fetch(`/api/assets/search?q=${encodeURIComponent(query)}&type=${kategori.toLowerCase()}`)
        .then(res => res.json())
        .then(res => {
          if (!dropdown) return;
          dropdown.innerHTML = '';

          if (!res.data || res.data.length === 0) {
            dropdown.innerHTML = '<div style="padding:12px 16px; font-size:12.5px; color:var(--slate-400); text-align:center;">Tidak ada aset yang sesuai.</div>';
            dropdown.style.display = 'block';
            return;
          }

          res.data.forEach(item => {
            const assetNo = item.nomor_asset || item.id || '';
            const assetDesc = item.deskripsi_asset || item.desc || '';
            const assetSn = item.serial_number || item.sn || '-';
            const np = item.nilai_perolehan !== undefined ? item.nilai_perolehan : (item.raw_np || 0);
            const nbv = item.nbv !== undefined ? item.nbv : (item.raw_nbv || 0);

            const row = document.createElement('div');
            row.className = 'dropdown-item-row';
            row.innerHTML = `
              <div class="dropdown-item-header">
                <i class="fa-solid fa-cube"></i> <strong>${assetNo}</strong> &bull; ${assetDesc}
              </div>
              <div class="dropdown-item-sub">
                SN: ${assetSn} | Nilai: Rp ${formatRibuan(np)} | NBV: Rp ${formatRibuan(nbv)}
              </div>
            `;
            row.onclick = () => pilihAsetAdj(item);
            dropdown.appendChild(row);
          });

          dropdown.style.display = 'block';
        })
        .catch(err => {
          console.error(err);
        });
    }, 250);
  }

  function bukaDropdownAdj() {
    const q = document.getElementById('searchAdjInput').value.trim();
    if (q.length > 0) cariAsetAdj();
  }

  function pilihAsetAdj(item) {
    const assetNo = item.nomor_asset || item.id || '';
    const assetDesc = item.deskripsi_asset || item.desc || '';
    const assetSn = (item.serial_number && item.serial_number !== '-') ? item.serial_number : (item.sn && item.sn !== '-' ? item.sn : '');
    const assetQty = item.qty_buku !== undefined ? item.qty_buku : (item.qty || 0);
    const np = item.nilai_perolehan !== undefined ? item.nilai_perolehan : (item.raw_np || 0);
    const ad = item.akumulasi_depresiasi !== undefined ? item.akumulasi_depresiasi : (item.raw_ad || 0);

    document.getElementById('searchAdjInput').value = `${assetNo} - ${assetDesc}`;
    document.getElementById('adjAssetNo').value = assetNo;
    document.getElementById('adjDesc').value = assetDesc;
    document.getElementById('adjSn').value = assetSn;
    document.getElementById('adjQty').value = assetQty;

    document.getElementById('adjNilai').value = formatRibuan(np);
    document.getElementById('adjDepresiasi').value = formatRibuan(ad);
    hitungNBVAdj();

    const dropdown = document.getElementById('dropdownAdj');
    if (dropdown) dropdown.style.display = 'none';
  }

  function resetPencarianAdj() {
    document.getElementById('searchAdjInput').value = '';
    document.getElementById('adjAssetNo').value = '';
    document.getElementById('adjDesc').value = '';
    document.getElementById('adjSn').value = '';
    document.getElementById('adjQty').value = '';
    document.getElementById('adjNilai').value = '';
    document.getElementById('adjDepresiasi').value = '';
    document.getElementById('adjNbv').value = '';
    document.getElementById('clearSearchAdjBtn').style.display = 'none';
    document.getElementById('dropdownAdj').style.display = 'none';
  }

  function submitAdjustment() {
    const assetNo = document.getElementById('adjAssetNo').value;
    if (!assetNo) {
      return showModal('error', 'Aset Belum Dipilih', 'Silakan pilih aset yang ingin disesuaikan dari autocomplete.');
    }

    hitungNBVAdj();
    const kategori = document.getElementById('adjKategori').value;
    const np = document.getElementById('rawAdjNilai').value;
    const ad = document.getElementById('rawAdjDepresiasi').value;
    const nbv = document.getElementById('rawAdjNbv').value;

    showLoading(true);

    fetch("{{ route('asset.adjustment.update') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      },
      body: JSON.stringify({
        kategori_db: kategori,
        nomor_asset: assetNo,
        nilai_perolehan: np,
        akumulasi_depresiasi: ad,
        nbv: nbv
      })
    })
    .then(res => res.json())
    .then(res => {
      showLoading(false);
      if (res.status === 'success') {
        showModal('success', 'Adjustment Berhasil', res.message, 'center', () => {
          resetPencarianAdj();
        });
      } else {
        showModal('error', 'Gagal Update Nilai', res.message || 'Terjadi kendala.');
      }
    })
    .catch(err => {
      showLoading(false);
      console.error(err);
      showModal('error', 'Kesalahan Sistem', 'Tidak dapat terhubung ke server.');
    });
  }

  function handleMassAdjustmentUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    showLoading(true);
    const formData = new FormData();
    formData.append('file_excel', file);

    fetch("{{ route('asset.mass_adjustment') }}", {
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
        msg += `Berhasil Disesuaikan: <strong style="color:var(--success-600);">${res.updated || 0}</strong><br>`;
        if (res.not_found > 0) {
          msg += `Tidak Ditemukan: <strong style="color:var(--warning-500);">${res.not_found}</strong><br>`;
        }
        showModal('success', 'Mass Adjustment Selesai', msg, 'left', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        showModal('error', 'Gagal Upload Mass Adjustment', res.message || 'Gagal memproses file.');
      }
    })
    .catch(err => {
      showLoading(false);
      event.target.value = '';
      console.error(err);
      showModal('error', 'Kesalahan Server', 'Gagal mengunggah file spreadsheet.');
    });
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('dropdownAdj');
    const searchInput = document.getElementById('searchAdjInput');
    if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
      dropdown.style.display = 'none';
    }
  });
</script>
@endpush
