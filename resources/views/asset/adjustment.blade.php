@extends('layouts.app')

@section('title', 'Asset Adjustment')

@section('content')
<div id="viewAdjustment" class="view-section active">
  @include('partials.header', ['title' => 'Asset Adjustment'])

  <div class="form-content">
    <!-- 1. Mass Adjustment Excel Section -->
    <div style="background: var(--main-blue-light); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cce0f0;">
      <h4 style="font-size: 13px; color: var(--main-blue); margin-bottom: 8px; font-weight: 600;">
        <i class="fa-solid fa-file-pen" style="color:#f39c12;"></i> Penyesuaian Nilai Masal (Excel / CSV Backend Upload)
      </h4>
      <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
        Unduh template data aset terkini dari server, perbarui nilai NP/AD/NBV, lalu upload kembali file tersebut.
      </p>

      <div class="grid-2" style="gap: 10px;">
        <a href="{{ route('asset.template', 'adjustment') }}" class="btn-primary" style="background:#27ae60; font-size:11px; padding:8px 5px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-download" style="margin-right:4px;"></i> Ekspor Template
        </a>

        <label class="btn-primary" style="background:var(--main-yellow); color:var(--main-blue); font-size:11px; padding:8px 5px; cursor:pointer; text-align:center; display:flex; align-items:center; justify-content:center; margin:0;">
          <i class="fa-solid fa-file-arrow-up" style="margin-right:4px;"></i> Upload Hasil
          <input type="file" id="fileMassAdj" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassAdjustmentUpload(event)">
        </label>
      </div>
    </div>

    <div style="text-align: center; margin: 15px 0 10px; position: relative;">
      <hr style="border: 0; border-top: 1px solid var(--border-color);">
      <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 10px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
        ATAU UPDATE SATUAN
      </span>
    </div>

    <!-- 2. Single Adjustment Form -->
    <form id="formAdjustment">
      @csrf

      <div class="form-group">
        <label>Kategori Database <span style="color:red">*</span></label>
        <select id="adjKategori" name="kategori_db" class="form-control" onchange="resetPencarianAdj()">
          <option value="INTERNAL" selected>🏭 Internal Database</option>
          <option value="EXTERNAL">🚚 External Database</option>
        </select>
      </div>

      <!-- Pencarian Aset -->
      <div class="form-group">
        <label>Pencarian Aset yang Akan Di-adjust</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-magnifying-glass icon-left"></i>
          <input type="text" id="searchAdjInput" class="form-control" style="padding-right: 35px;"
            placeholder="Ketik No / Nama / SN..." autocomplete="off" onkeyup="cariAsetAdj()" onfocus="bukaDropdownAdj()">
          <i class="fa-solid fa-circle-xmark icon-right" id="clearSearchAdjBtn" style="display:none; color:#e74c3c; font-size:18px; cursor:pointer;" onclick="resetPencarianAdj()"></i>
        </div>
        <div id="dropdownAdj" class="dropdown-list" style="display:none;"></div>
        <input type="hidden" id="adjAssetNo" name="nomor_asset">
      </div>

      <div class="form-group">
        <label>Deskripsi Aset</label>
        <input type="text" id="adjDesc" class="form-control" readonly placeholder="-">
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Serial Number</label>
          <input type="text" id="adjSn" class="form-control" readonly placeholder="-">
        </div>
        <div class="form-group">
          <label>Qty Buku</label>
          <input type="number" id="adjQty" class="form-control" readonly placeholder="0">
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Nilai Perolehan Baru (Rp) <span style="color:red">*</span></label>
          <input type="text" id="adjNilai" class="form-control" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBVAdj();">
          <input type="hidden" id="rawAdjNilai" value="0">
        </div>
        <div class="form-group">
          <label>Akum. Depresiasi Baru (Rp) <span style="color:red">*</span></label>
          <input type="text" id="adjDepresiasi" class="form-control" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBVAdj();">
          <input type="hidden" id="rawAdjDepresiasi" value="0">
        </div>
      </div>

      <div class="form-group">
        <label>Net Book Value Baru / NBV (Rp)</label>
        <input type="text" id="adjNbv" class="form-control" readonly placeholder="0" style="background:#f8f9fa; font-weight:600; color:#27ae60;">
        <input type="hidden" id="rawAdjNbv" value="0">
      </div>

      <button type="button" id="btnAdjSubmit" class="btn-primary" onclick="submitAdjustment()">
        <i class="fa-solid fa-check-double"></i> Simpan Penyesuaian Nilai
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function hitungNBVAdj() {
    const rawNp = document.getElementById('adjNilai').value.replace(/[^0-9]/g, '');
    const rawAd = document.getElementById('adjDepresiasi').value.replace(/[^0-9]/g, '');
    
    const np = Number(rawNp) || 0;
    const ad = Number(rawAd) || 0;
    const nbv = np - ad;

    document.getElementById('rawAdjNilai').value = np;
    document.getElementById('rawAdjDepresiasi').value = ad;
    document.getElementById('rawAdjNbv').value = nbv;
    document.getElementById('adjNbv').value = formatRibuan(nbv);
  }

  function bukaDropdownAdj() {
    const text = document.getElementById('searchAdjInput').value.trim();
    if (text.length > 0) document.getElementById('dropdownAdj').style.display = 'block';
  }

  let searchTimer = null;
  function cariAsetAdj() {
    const kat = document.getElementById('adjKategori').value;
    const query = document.getElementById('searchAdjInput').value.trim();
    document.getElementById('clearSearchAdjBtn').style.display = query.length > 0 ? 'block' : 'none';

    if (query === '') {
      document.getElementById('dropdownAdj').style.display = 'none';
      return;
    }

    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
      try {
        const res = await fetch(`/api/assets/search?kategori=${kat}&query=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderDropdownAdj(json.data || []);
      } catch (err) {
        console.error("Search error:", err);
      }
    }, 200);
  }

  function renderDropdownAdj(items) {
    const box = document.getElementById('dropdownAdj');
    box.style.display = 'block';

    if (items.length === 0) {
      box.innerHTML = '<div class="dropdown-item" style="color:#e74c3c;text-align:center;">Aset tidak ditemukan</div>';
      return;
    }

    let html = '';
    items.forEach(item => {
      let safeId = item.id.replace(/'/g, "\\'");
      let safeDesc = item.desc.replace(/'/g, "\\'");
      let safeSn = (item.sn || '-').replace(/'/g, "\\'");
      let snLabel = item.sn && item.sn !== '-' ? `<span class="sn-badge">SN: ${item.sn}</span>` : '';
      
      html += `<div class="dropdown-item" onclick="pilihAsetAdj('${safeId}', '${safeDesc}', '${safeSn}', ${item.qty}, ${item.raw_np}, ${item.raw_ad}, ${item.raw_nbv})">
        <strong>${item.id} ${snLabel}</strong>
        <span>${item.desc}</span>
      </div>`;
    });
    box.innerHTML = html;
  }

  function pilihAsetAdj(id, desc, sn, qty, np, ad, nbv) {
    document.getElementById('searchAdjInput').value = `${id} - ${desc}`;
    document.getElementById('adjAssetNo').value = id;
    document.getElementById('adjDesc').value = desc;
    document.getElementById('adjSn').value = sn;
    document.getElementById('adjQty').value = qty;

    document.getElementById('adjNilai').value = formatRibuan(np);
    document.getElementById('adjDepresiasi').value = formatRibuan(ad);
    document.getElementById('adjNbv').value = formatRibuan(nbv);

    document.getElementById('rawAdjNilai').value = np;
    document.getElementById('rawAdjDepresiasi').value = ad;
    document.getElementById('rawAdjNbv').value = nbv;

    document.getElementById('clearSearchAdjBtn').style.display = 'block';
    document.getElementById('dropdownAdj').style.display = 'none';
    document.getElementById('adjNilai').focus();
  }

  function resetPencarianAdj() {
    ['searchAdjInput', 'adjAssetNo', 'adjDesc', 'adjSn', 'adjQty', 'adjNilai', 'adjDepresiasi', 'adjNbv'].forEach(id => document.getElementById(id).value = '');
    ['rawAdjNilai', 'rawAdjDepresiasi', 'rawAdjNbv'].forEach(id => document.getElementById(id).value = '0');
    document.getElementById('clearSearchAdjBtn').style.display = 'none';
    document.getElementById('dropdownAdj').style.display = 'none';
  }

  async function submitAdjustment() {
    const kat = document.getElementById('adjKategori').value;
    const no = document.getElementById('adjAssetNo').value;
    const np = Number(document.getElementById('rawAdjNilai').value);
    const ad = Number(document.getElementById('rawAdjDepresiasi').value);
    const nbv = Number(document.getElementById('rawAdjNbv').value);

    if (!no) {
      return showModal('error', 'Pilih Aset', 'Cari dan pilih aset yang akan disesuaikan nilainya terlebih dahulu.');
    }

    showLoading(true);

    try {
      const res = await fetch("{{ route('asset.update') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          kategori_db: kat,
          nomor_asset: no,
          nilai_perolehan: np,
          akumulasi_depresiasi: ad,
          nbv: nbv,
        })
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Adjustment Berhasil!', json.message, 'center', () => {
          resetPencarianAdj();
        });
      } else {
        throw new Error(json.message || "Gagal melakukan penyesuaian aset.");
      }
    } catch (err) {
      showModal('error', 'Gagal Adjustment', err.message);
    } finally {
      showLoading(false);
    }
  }

  async function handleMassAdjustmentUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    showLoading(true);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch("{{ route('asset.mass_adjustment') }}", {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: formData
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Penyesuaian Masal Berhasil!', json.message, 'center', () => {
          window.location.reload();
        });
      } else {
        throw new Error(json.message || "Gagal memproses penyesuaian masal.");
      }
    } catch (err) {
      showModal('error', 'Gagal Upload Penyesuaian', err.message);
    } finally {
      document.getElementById('fileMassAdj').value = '';
      showLoading(false);
    }
  }

  document.addEventListener('click', function (e) {
    if (e.target.id !== 'searchAdjInput' && e.target.id !== 'dropdownAdj') {
      const box = document.getElementById('dropdownAdj');
      if (box) box.style.display = 'none';
    }
  });
</script>
@endpush
