@extends('layouts.app')

@section('title', 'Fixed Asset Retirements')

@section('content')
<div id="viewRetirement" class="view-section active">
  @include('partials.header', ['title' => 'Fixed Asset Retirements'])

  <div class="form-content">
    <!-- 1. Mass Retirement Excel Section -->
    <div style="background: var(--main-blue-light); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cce0f0;">
      <h4 style="font-size: 13px; color: var(--main-blue); margin-bottom: 8px; font-weight: 600;">
        <i class="fa-solid fa-trash-can" style="color:#e74c3c;"></i> Hapus / Potong Aset Masal (Excel / CSV Backend Upload)
      </h4>
      <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
        Gunakan template disposal masal untuk menghapus aset atau memotong kuantiti stok dari sistem SAP. Diproses di backend server.
      </p>

      <div class="grid-2" style="gap: 10px;">
        <a href="{{ route('asset.template', 'retirement') }}" class="btn-primary" style="background:#27ae60; font-size:11px; padding:8px 5px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-download" style="margin-right:4px;"></i> Unduh Template
        </a>

        <label class="btn-primary" style="background:var(--main-yellow); color:var(--main-blue); font-size:11px; padding:8px 5px; cursor:pointer; text-align:center; display:flex; align-items:center; justify-content:center; margin:0;">
          <i class="fa-solid fa-file-arrow-up" style="margin-right:4px;"></i> Upload File
          <input type="file" id="fileMassRet" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassRetirementUpload(event)">
        </label>
      </div>
    </div>

    <div style="text-align: center; margin: 15px 0 10px; position: relative;">
      <hr style="border: 0; border-top: 1px solid var(--border-color);">
      <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 10px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
        ATAU DISPOSAL SATUAN
      </span>
    </div>

    <!-- 2. Single Retirement Form -->
    <form id="formRetirement">
      @csrf

      <div class="form-group">
        <label>Kategori Database <span style="color:red">*</span></label>
        <select id="retKategori" name="kategori_db" class="form-control" onchange="resetPencarianRet()">
          <option value="INTERNAL" selected>🏭 Internal Database</option>
          <option value="EXTERNAL">🚚 External Database</option>
        </select>
      </div>

      <!-- Pencarian Aset -->
      <div class="form-group">
        <label>Pencarian Aset yang Akan Di-disposal</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-magnifying-glass icon-left"></i>
          <input type="text" id="searchRetInput" class="form-control" style="padding-right: 35px;"
            placeholder="Ketik No / Nama / SN..." autocomplete="off" onkeyup="cariAsetRet()" onfocus="bukaDropdownRet()">
          <i class="fa-solid fa-circle-xmark icon-right" id="clearSearchRetBtn" style="display:none; color:#e74c3c; font-size:18px; cursor:pointer;" onclick="resetPencarianRet()"></i>
        </div>
        <div id="dropdownRetirement" class="dropdown-list" style="display:none;"></div>
        <input type="hidden" id="retAssetNo" name="nomor_asset">
      </div>

      <div class="form-group">
        <label>Deskripsi Aset</label>
        <input type="text" id="retDesc" class="form-control" readonly placeholder="-">
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Qty Saat Ini</label>
          <input type="number" id="retQtyCurrent" class="form-control" readonly placeholder="0">
        </div>
        <div class="form-group">
          <label>NBV Saat Ini (Rp)</label>
          <input type="text" id="retNbvCurrent" class="form-control" readonly placeholder="0" style="color:#27ae60; font-weight:600;">
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Qty Disposal <span style="color:red">*</span></label>
          <input type="number" id="retQtyInput" name="qty_disposal" class="form-control" placeholder="Qty dihapus..." min="1" required>
        </div>
        <div class="form-group">
          <label>Nomor Dokumen SAP <span style="color:red">*</span></label>
          <input type="text" id="retDocSap" name="dokumen_sap" class="form-control" placeholder="No Dokumen SAP..." required>
        </div>
      </div>

      <div class="form-group">
        <label>Catatan / Alasan Retirement <span style="color:red">*</span></label>
        <textarea id="retCatatan" name="catatan" class="form-control" rows="2" placeholder="Contoh: Afkir, Kerusakan fatal, Penjualan aset..." required></textarea>
      </div>

      <button type="button" id="btnRetSubmit" class="btn-primary" style="background:#e74c3c;" onclick="konfirmasiRetirement()">
        <i class="fa-solid fa-triangle-exclamation"></i> Verifikasi & Proses Disposal
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let selectedAsset = null;

  function bukaDropdownRet() {
    const text = document.getElementById('searchRetInput').value.trim();
    if (text.length > 0) document.getElementById('dropdownRetirement').style.display = 'block';
  }

  let searchRetTimer = null;
  function cariAsetRet() {
    const kat = document.getElementById('retKategori').value;
    const query = document.getElementById('searchRetInput').value.trim();
    document.getElementById('clearSearchRetBtn').style.display = query.length > 0 ? 'block' : 'none';

    if (query === '') {
      document.getElementById('dropdownRetirement').style.display = 'none';
      return;
    }

    clearTimeout(searchRetTimer);
    searchRetTimer = setTimeout(async () => {
      try {
        const res = await fetch(`/api/assets/search?kategori=${kat}&query=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderDropdownRet(json.data || []);
      } catch (err) {
        console.error("Search error:", err);
      }
    }, 200);
  }

  function renderDropdownRet(items) {
    const box = document.getElementById('dropdownRetirement');
    box.style.display = 'block';

    if (items.length === 0) {
      box.innerHTML = '<div class="dropdown-item" style="color:#e74c3c;text-align:center;">Aset tidak ditemukan</div>';
      return;
    }

    let html = '';
    items.forEach(item => {
      let safeId = item.id.replace(/'/g, "\\'");
      let safeDesc = item.desc.replace(/'/g, "\\'");
      let snLabel = item.sn && item.sn !== '-' ? `<span class="sn-badge">SN: ${item.sn}</span>` : '';
      
      html += `<div class="dropdown-item" onclick="pilihAsetRet('${safeId}', '${safeDesc}', ${item.qty}, ${item.raw_nbv})">
        <strong>${item.id} ${snLabel}</strong>
        <span>${item.desc}</span>
      </div>`;
    });
    box.innerHTML = html;
  }

  function pilihAsetRet(id, desc, qty, nbv) {
    selectedAsset = { id, desc, qty, nbv };
    document.getElementById('searchRetInput').value = `${id} - ${desc}`;
    document.getElementById('retAssetNo').value = id;
    document.getElementById('retDesc').value = desc;
    document.getElementById('retQtyCurrent').value = qty;
    document.getElementById('retNbvCurrent').value = formatRibuan(nbv);

    document.getElementById('clearSearchRetBtn').style.display = 'block';
    document.getElementById('dropdownRetirement').style.display = 'none';
    document.getElementById('retQtyInput').focus();
  }

  function resetPencarianRet() {
    ['searchRetInput', 'retAssetNo', 'retDesc', 'retQtyCurrent', 'retNbvCurrent', 'retQtyInput', 'retDocSap', 'retCatatan'].forEach(id => document.getElementById(id).value = '');
    selectedAsset = null;
    document.getElementById('clearSearchRetBtn').style.display = 'none';
    document.getElementById('dropdownRetirement').style.display = 'none';
  }

  function konfirmasiRetirement() {
    if (!selectedAsset) {
      return showModal('error', 'Pilih Aset', 'Silakan cari dan pilih aset yang akan di-disposal terlebih dahulu.');
    }

    const qtyInput = Number(document.getElementById('retQtyInput').value);
    const docSap = document.getElementById('retDocSap').value.trim();
    const catatan = document.getElementById('retCatatan').value.trim();

    let errs = [];
    if (!qtyInput || qtyInput <= 0) errs.push("• Qty Disposal harus minimal 1.");
    if (qtyInput > selectedAsset.qty) errs.push(`• Qty Disposal tidak boleh melebihi stok yang ada (${selectedAsset.qty}).`);
    if (!docSap) errs.push("• Nomor Dokumen SAP wajib diisi.");
    if (!catatan) errs.push("• Catatan Retirement wajib diisi.");

    if (errs.length > 0) {
      return showModal('error', 'Data Belum Lengkap', errs.join('<br>'), 'left');
    }

    let nbvPotong = Math.round(selectedAsset.nbv * (qtyInput / selectedAsset.qty));

    document.getElementById('confirmTitle').innerText = 'Konfirmasi Disposal SAP';
    document.getElementById('confirmDesc').innerHTML = `
      <div style="font-size: 13px; line-height: 1.8;">
        <strong>Mohon Verifikasi Data:</strong><br><br>
        <table style="width:100%; border-collapse: collapse;">
          <tr><td style="padding: 2px 0; color: var(--text-muted); width: 45%;">Nomor Aset</td><td style="padding: 2px 0; font-weight:600;">: ${selectedAsset.id}</td></tr>
          <tr><td style="padding: 2px 0; color: var(--text-muted); vertical-align:top;">Nama Aset</td><td style="padding: 2px 0; font-weight:600; vertical-align:top;">: ${selectedAsset.desc}</td></tr>
          <tr><td style="padding: 2px 0; color: var(--text-muted);">Qty Dihapus</td><td style="padding: 2px 0; font-weight:600; color:#e74c3c;">: ${qtyInput} Unit</td></tr>
          <tr><td style="padding: 2px 0; color: var(--text-muted);">Sisa Qty Nanti</td><td style="padding: 2px 0; font-weight:600;">: ${selectedAsset.qty - qtyInput} Unit</td></tr>
          <tr><td style="padding: 2px 0; color: var(--text-muted);">Potongan NBV</td><td style="padding: 2px 0; font-weight:600; color:#e74c3c;">: Rp ${formatRibuan(nbvPotong)}</td></tr>
        </table>
      </div>
    `;

    document.getElementById('btnConfirmAction').onclick = eksekusiRetirement;
    document.getElementById('confirmModal').style.display = 'flex';
  }

  async function eksekusiRetirement() {
    tutupConfirmModal();
    showLoading(true);

    const kat = document.getElementById('retKategori').value;
    const no = document.getElementById('retAssetNo').value;
    const qty = Number(document.getElementById('retQtyInput').value);
    const docSap = document.getElementById('retDocSap').value.trim();
    const catatan = document.getElementById('retCatatan').value.trim();

    try {
      const res = await fetch("{{ route('asset.retirement.process') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          kategori_db: kat,
          nomor_asset: no,
          qty_disposal: qty,
          dokumen_sap: docSap,
          catatan: catatan,
        })
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Kerja Bagus!', json.message, 'center', () => {
          resetPencarianRet();
        });
      } else {
        throw new Error(json.message || "Gagal memproses disposal aset.");
      }
    } catch (err) {
      showModal('error', 'Sistem Gagal', err.message);
    } finally {
      showLoading(false);
    }
  }

  async function handleMassRetirementUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    showLoading(true);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch("{{ route('asset.mass_retirement') }}", {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: formData
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Disposal Masal Berhasil!', json.message, 'center', () => {
          window.location.reload();
        });
      } else {
        throw new Error(json.message || "Gagal memproses file disposal masal.");
      }
    } catch (err) {
      showModal('error', 'Gagal Upload Disposal', err.message);
    } finally {
      document.getElementById('fileMassRet').value = '';
      showLoading(false);
    }
  }

  document.addEventListener('click', function (e) {
    if (e.target.id !== 'searchRetInput' && e.target.id !== 'dropdownRetirement') {
      const box = document.getElementById('dropdownRetirement');
      if (box) box.style.display = 'none';
    }
  });
</script>
@endpush
