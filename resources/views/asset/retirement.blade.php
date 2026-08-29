@extends('layouts.app')

@section('title', 'Mass Asset Retirement')

@section('content')
<div style="max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <!-- 1. Mass Retirement Excel Section -->
  <div class="card-panel" style="background: linear-gradient(135deg, rgba(254, 242, 242, 0.6) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1.5px solid var(--danger-light);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div style="max-width: 580px;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--danger-light); color: var(--danger-600); padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; margin-bottom: 8px;">
          <i class="fa-solid fa-trash-can"></i> Batch Asset Disposal
        </div>
        <h3 style="font-size: 17px; font-weight: 800; color: var(--danger-600); margin-bottom: 4px;">
          Disposal / Pemotongan Aset Masal via Excel (.xlsx)
        </h3>
        <p style="font-size: 13px; color: var(--slate-600); line-height: 1.5;">
          Gunakan template disposal masal untuk menghapus aset yang sudah afkir atau memotong kuantiti stok dari sistem secara tersinkronisasi.
        </p>
      </div>

      <div style="display: flex; gap: 10px;">
        <a href="{{ route('asset.template', 'retirement') }}" class="btn-enterprise btn-enterprise-outline" style="background: #ffffff;">
          <i class="fa-solid fa-download" style="color: var(--danger-600);"></i> Unduh Template Excel
        </a>

        <label class="btn-enterprise btn-enterprise-danger" style="cursor: pointer; margin: 0;">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload Hasil .xlsx
          <input type="file" id="fileMassRet" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassRetirementUpload(event)">
        </label>
      </div>
    </div>
  </div>

  <!-- 2. Manual Retirement Form -->
  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-circle-exclamation" style="color: var(--danger-500);"></i> Proses Disposal Satuan (Scrap / Sale / Write-Off)
        </h2>
        <p class="card-subtitle-text">Pilih aset yang ingin di-retire, tentukan jumlah kuantiti yang dipotong, nomor dokumen SAP, dan alasan disposal</p>
      </div>
    </div>

    <form id="formRetirement">
      @csrf

      <div class="form-group-modern">
        <label for="retKategori" class="form-label-modern">Kategori Database <span class="req">*</span></label>
        <select id="retKategori" name="kategori_db" class="form-control-modern" onchange="resetPencarianRet()">
          <option value="INTERNAL" selected>🏭 Internal Database (Pabrik & Kantor)</option>
          <option value="EXTERNAL">🚚 External Database (Agen & Distributor Channel INN dan Reguler)</option>
        </select>
      </div>

      <!-- Pencarian Aset -->
      <div class="form-group-modern">
        <label for="searchRetInput" class="form-label-modern">Cari Aset yang Akan Di-disposal <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
          <input type="text" id="searchRetInput" class="form-control-modern" placeholder="Ketik No Aset / Nama / Serial Number..." autocomplete="off" onkeyup="cariAsetRet()" onfocus="bukaDropdownRet()">
          <i class="fa-solid fa-circle-xmark" id="clearSearchRetBtn" style="display:none; position: absolute; right: 14px; color: var(--danger-500); font-size: 16px; cursor: pointer;" onclick="resetPencarianRet()"></i>
        </div>
        <div id="dropdownRetirement" class="search-dropdown-box"></div>
        <input type="hidden" id="retAssetNo" name="nomor_asset">
      </div>

      <div class="form-group-modern">
        <label class="form-label-modern">Deskripsi Aset Terpilih</label>
        <div class="input-container">
          <i class="fa-solid fa-box-open input-icon-left"></i>
          <input type="text" id="retDesc" class="form-control-modern" readonly placeholder="Otomatis terisi saat aset dipilih...">
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label class="form-label-modern">Kuantitas Saat Ini di Sistem</label>
          <div class="input-container">
            <i class="fa-solid fa-cubes input-icon-left"></i>
            <input type="number" id="retQtyCurrent" class="form-control-modern" readonly placeholder="0">
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Net Book Value (NBV) Saat Ini</label>
          <div class="input-container">
            <i class="fa-solid fa-wallet input-icon-left" style="color: var(--success-600);"></i>
            <input type="text" id="retNbvCurrent" class="form-control-modern" readonly placeholder="0" style="color: var(--success-600); font-weight: 700; background: var(--success-light);">
          </div>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label for="retQtyInput" class="form-label-modern">Kuantitas yang Di-disposal <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-calculator input-icon-left" style="color: var(--danger-500);"></i>
            <input type="number" id="retQtyInput" name="qty_disposal" class="form-control-modern" placeholder="Jumlah unit yang dihapus..." min="1" required>
          </div>
        </div>

        <div class="form-group-modern">
          <label for="retDocSap" class="form-label-modern">Nomor Referensi Dokumen SAP <span class="req">*</span></label>
          <div class="input-container">
            <i class="fa-solid fa-file-contract input-icon-left"></i>
            <input type="text" id="retDocSap" name="dokumen_sap" class="form-control-modern" placeholder="Contoh: SAP-RET-2026-001" required>
          </div>
        </div>
      </div>

      <div class="form-group-modern">
        <label for="retCatatan" class="form-label-modern">Alasan / Catatan Berita Acara Disposal <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-comment-dots input-icon-left"></i>
          <input type="text" id="retCatatan" name="catatan" class="form-control-modern" placeholder="Contoh: Afkir kerusakan berat, hasil lelang, serah terima vendor..." required>
        </div>
      </div>

      <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('asset.index') }}" class="btn-enterprise btn-enterprise-outline">
          Batal & Kembali
        </a>
        <button type="button" id="btnRetSubmit" class="btn-enterprise btn-enterprise-danger" onclick="konfirmasiRetirement()" style="min-width: 200px;">
          <i class="fa-solid fa-triangle-exclamation"></i> Eksekusi Disposal Aset
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  let searchTimer = null;

  function cariAsetRet() {
    clearTimeout(searchTimer);
    const query = document.getElementById('searchRetInput').value.trim();
    const kategori = document.getElementById('retKategori').value;
    const dropdown = document.getElementById('dropdownRetirement');
    const clearBtn = document.getElementById('clearSearchRetBtn');

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
            dropdown.innerHTML = '<div style="padding:12px 16px; font-size:12.5px; color:var(--slate-400); text-align:center;">Tidak ada aset yang ditemukan.</div>';
            dropdown.style.display = 'block';
            return;
          }

          res.data.forEach(item => {
            const assetNo = item.nomor_asset || item.id || '';
            const assetDesc = item.deskripsi_asset || item.desc || '';
            const assetSn = item.serial_number || item.sn || '-';
            const assetQty = item.qty_buku !== undefined ? item.qty_buku : (item.qty || 0);
            const nbv = item.nbv !== undefined ? item.nbv : (item.raw_nbv || 0);

            const row = document.createElement('div');
            row.className = 'dropdown-item-row';
            row.innerHTML = `
              <div class="dropdown-item-header">
                <i class="fa-solid fa-box"></i> <strong>${assetNo}</strong> &bull; ${assetDesc}
              </div>
              <div class="dropdown-item-sub">
                SN: ${assetSn} | Qty Buku: ${assetQty} | NBV: Rp ${formatRibuan(nbv)}
              </div>
            `;
            row.onclick = () => pilihAsetRet(item);
            dropdown.appendChild(row);
          });

          dropdown.style.display = 'block';
        })
        .catch(err => {
          console.error(err);
        });
    }, 250);
  }

  function bukaDropdownRet() {
    const q = document.getElementById('searchRetInput').value.trim();
    if (q.length > 0) cariAsetRet();
  }

  function pilihAsetRet(item) {
    const assetNo = item.nomor_asset || item.id || '';
    const assetDesc = item.deskripsi_asset || item.desc || '';
    const assetQty = item.qty_buku !== undefined ? item.qty_buku : (item.qty || 0);
    const nbv = item.nbv !== undefined ? item.nbv : (item.raw_nbv || 0);

    document.getElementById('searchRetInput').value = `${assetNo} - ${assetDesc}`;
    document.getElementById('retAssetNo').value = assetNo;
    document.getElementById('retDesc').value = assetDesc;
    document.getElementById('retQtyCurrent').value = assetQty;
    document.getElementById('retNbvCurrent').value = `Rp ${formatRibuan(nbv)}`;

    const dropdown = document.getElementById('dropdownRetirement');
    if (dropdown) dropdown.style.display = 'none';
  }

  function resetPencarianRet() {
    document.getElementById('searchRetInput').value = '';
    document.getElementById('retAssetNo').value = '';
    document.getElementById('retDesc').value = '';
    document.getElementById('retQtyCurrent').value = '';
    document.getElementById('retNbvCurrent').value = '';
    document.getElementById('retQtyInput').value = '';
    document.getElementById('retDocSap').value = '';
    document.getElementById('retCatatan').value = '';
    document.getElementById('clearSearchRetBtn').style.display = 'none';
    document.getElementById('dropdownRetirement').style.display = 'none';
  }

  function konfirmasiRetirement() {
    const assetNo = document.getElementById('retAssetNo').value;
    const qtyDisposal = Number(document.getElementById('retQtyInput').value) || 0;
    const qtyCurrent = Number(document.getElementById('retQtyCurrent').value) || 0;
    const docSap = document.getElementById('retDocSap').value.trim();
    const catatan = document.getElementById('retCatatan').value.trim();

    if (!assetNo) {
      return showModal('error', 'Aset Belum Dipilih', 'Silakan pilih aset yang ingin di-disposal dari autocomplete.');
    }
    if (qtyDisposal <= 0) {
      return showModal('error', 'Qty Disposal Salah', 'Kuantitas disposal minimal 1 unit.');
    }
    if (qtyDisposal > qtyCurrent) {
      return showModal('error', 'Qty Melebihi Batas', `Kuantitas disposal (${qtyDisposal}) tidak boleh melebihi kuantiti stok saat ini (${qtyCurrent}).`);
    }
    if (!docSap || !catatan) {
      return showModal('error', 'Data Belum Lengkap', 'Nomor dokumen SAP dan alasan disposal wajib diisi.');
    }

    const confirmModal = document.getElementById('confirmModal');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmDesc = document.getElementById('confirmDesc');
    const btnAction = document.getElementById('confirmBtnAction');

    confirmTitle.innerText = 'Konfirmasi Disposal Aset';
    confirmDesc.innerHTML = `Apakah Anda yakin ingin memotong <strong>${qtyDisposal} unit</strong> dari aset <strong>${assetNo}</strong>?<br><br>Tindakan ini akan tercatat dalam riwayat retirement permanen.`;
    
    btnAction.className = 'btn-enterprise btn-enterprise-danger';
    btnAction.innerText = 'Ya, Eksekusi Disposal';
    btnAction.onclick = () => {
      tutupConfirmModal();
      eksekusiRetirement(assetNo, qtyDisposal, docSap, catatan);
    };

    confirmModal.style.display = 'flex';
  }

  function eksekusiRetirement(assetNo, qtyDisposal, docSap, catatan) {
    const kategori = document.getElementById('retKategori').value;
    showLoading(true);

    fetch("{{ route('asset.retirement.store') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      },
      body: JSON.stringify({
        kategori_db: kategori,
        nomor_asset: assetNo,
        qty_disposal: qtyDisposal,
        dokumen_sap: docSap,
        catatan: catatan
      })
    })
    .then(res => res.json())
    .then(res => {
      showLoading(false);
      if (res.status === 'success') {
        showModal('success', 'Disposal Berhasil Diproses', res.message, 'center', () => {
          resetPencarianRet();
        });
      } else {
        showModal('error', 'Gagal Memproses Disposal', res.message || 'Terjadi kesalahan sistem.');
      }
    })
    .catch(err => {
      showLoading(false);
      console.error(err);
      showModal('error', 'Kesalahan Server', 'Tidak dapat terhubung ke server.');
    });
  }

  function handleMassRetirementUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    showLoading(true);
    const formData = new FormData();
    formData.append('file_excel', file);

    fetch("{{ route('asset.mass_retirement') }}", {
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
        msg += `Aset Terhapus Penuh: <strong style="color:var(--danger-500);">${res.deleted || 0}</strong><br>`;
        msg += `Aset Terpotong Qty: <strong style="color:var(--warning-500);">${res.reduced || 0}</strong><br>`;
        if (res.errors && res.errors.length > 0) {
          msg += `<br><span style="color:var(--danger-500); font-weight:700;">Catatan Error:</span><br>${res.errors.slice(0, 5).join('<br>')}`;
        }
        showModal('success', 'Mass Retirement Selesai', msg, 'left', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        showModal('error', 'Gagal Upload Mass Retirement', res.message || 'Gagal memproses file.');
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
    const dropdown = document.getElementById('dropdownRetirement');
    const searchInput = document.getElementById('searchRetInput');
    if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
      dropdown.style.display = 'none';
    }
  });
</script>
@endpush
