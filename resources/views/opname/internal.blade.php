@extends('layouts.app')

@section('title', 'Stock Opname Internal Assets')

@section('content')
<div id="viewApp" class="view-section active">
  @include('partials.header', ['title' => 'Stock opname Internal Assets'])

  <div class="form-content">
    <!-- Info Petugas & Status DB -->
    <div style="background: var(--main-blue-light); padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: var(--main-blue); display: flex; justify-content: space-between; align-items: center;">
      <span><i class="fa-solid fa-user-check"></i> <strong>{{ auth()->user()->nama_karyawan }}</strong></span>
      <span id="dbStatus" style="font-size: 11px; background: #27ae60; color: #fff; padding: 3px 8px; border-radius: 4px;">
        <i class="fa-solid fa-check"></i> DB Siap
      </span>
    </div>

    <form id="formOpnameInternal" enctype="multipart/form-data">
      @csrf

      <!-- Dokumentasi Foto (Fisik & Tagging) -->
      <div class="form-group">
        <label>Dokumentasi Opname <span style="color:red">*</span></label>
        <div class="grid-2">
          <div class="file-upload-wrapper" id="boxFisik">
            <i class="fa-solid fa-camera" style="font-size:22px; color:var(--main-blue); margin-bottom:5px;"></i>
            <p id="nameFisik" style="font-size:12px; color:var(--main-blue); font-weight:600;">Foto Aset (Fisik)</p>
            <input type="file" id="inputFotoFisik" accept="image/*" capture="environment" onchange="previewUploadFoto(event, 'previewFisik', 'nameFisik', 'boxFisik', 'fisik')">
            <img id="previewFisik" class="image-preview" alt="Foto Fisik">
          </div>

          <div class="file-upload-wrapper" id="boxTagging">
            <i class="fa-solid fa-qrcode" style="font-size:22px; color:var(--main-yellow-hover); margin-bottom:5px;"></i>
            <p id="nameTagging" style="font-size:12px; color:var(--main-yellow-hover); font-weight:600;">Foto Label (Tagging)</p>
            <input type="file" id="inputFotoTagging" accept="image/*" capture="environment" onchange="previewUploadFoto(event, 'previewTagging', 'nameTagging', 'boxTagging', 'tagging')">
            <img id="previewTagging" class="image-preview" alt="Foto Tagging">
          </div>
        </div>
      </div>

      <!-- Pencarian Aset -->
      <div class="form-group">
        <label>Pencarian Aset (No Aset / Nama / SN)</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-magnifying-glass icon-left"></i>
          <input type="text" id="searchInput" class="form-control" style="padding-right: 35px;"
            placeholder="Ketik No / Nama / SN atau Scan Barcode..." autocomplete="off" onkeyup="cariAset()" onfocus="bukaDropdown()">
          <i class="fa-solid fa-circle-xmark icon-right" id="clearSearchBtn" style="display:none; color:#e74c3c; font-size:18px; cursor:pointer;" onclick="resetPencarian()"></i>
        </div>
        <div id="customDropdown" class="dropdown-list" style="display:none;"></div>
        <input type="hidden" id="assetNo" name="nomor_asset">
      </div>

      <!-- Deskripsi Aset -->
      <div class="form-group">
        <label>Nama Aset</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-box-open icon-left"></i>
          <input type="text" id="assetDesc" name="deskripsi_asset" class="form-control" readonly placeholder="-">
        </div>
      </div>

      <!-- Serial Number & Qty Buku -->
      <div class="grid-2">
        <div class="form-group">
          <label>Serial Number</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-barcode icon-left"></i>
            <input type="text" id="assetSn" name="serial_number" class="form-control" readonly placeholder="-">
          </div>
        </div>
        <div class="form-group">
          <label>Qty Buku</label>
          <div class="input-wrapper">
            <i class="fa-solid fa-book icon-left"></i>
            <input type="number" id="qtyBuku" name="qty_buku" class="form-control" readonly placeholder="0">
          </div>
        </div>
      </div>

      <!-- Qty Fisik Aktual -->
      <div class="form-group">
        <label>Qty Fisik Aktual <span style="color:red">*</span></label>
        <div class="input-wrapper">
          <i class="fa-solid fa-clipboard-check icon-left" style="color:var(--main-blue);"></i>
          <input type="number" id="qtyFisik" name="qty_fisik" class="form-control" placeholder="Jumlah fisik..." required min="0">
        </div>
      </div>

      <!-- Kondisi & Status -->
      <div class="form-group">
        <label>Kondisi & Status <span style="color:red">*</span></label>
        <div class="grid-3">
          <select id="tagging" name="tagging" class="form-control" style="padding-left:10px;" required>
            <option value="" disabled selected>🏷️ Tagging?</option>
            <option value="Ada">✔️ Ada</option>
            <option value="Tidak Ada">❌ Tidak</option>
          </select>

          <select id="status" name="status_penggunaan" class="form-control" style="padding-left:10px;" required>
            <option value="" disabled selected>📌 Status?</option>
            <option value="Digunakan">🟢 Digunakan</option>
            <option value="Idle Sementara">🟡 Idle Sem.</option>
            <option value="Idle Permanen">🔴 Idle Perm.</option>
          </select>

          <select id="kondisi" name="kondisi" class="form-control" style="padding-left:10px;" required>
            <option value="" disabled selected>🛠️ Kondisi?</option>
            <option value="Baik">✔️ Baik</option>
            <option value="Rusak">❌ Rusak</option>
          </select>
        </div>
      </div>

      <!-- Detail Area Internal -->
      <div class="form-group">
        <label>Detail Area / Ruangan Internal <span style="color:red">*</span></label>
        <textarea id="lokasi" name="lokasi" class="form-control" rows="2" placeholder="Contoh: Rak B, Ruang Server, Area Produksi..." required></textarea>
      </div>

      <!-- Tombol Submit -->
      <button type="button" id="btnSubmit" class="btn-primary" onclick="submitOpnameInternal()">
        <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Data Opname
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let photoFisikBase64 = null;
  let photoTaggingBase64 = null;

  function bukaDropdown() {
    const text = document.getElementById('searchInput').value.trim();
    if (text.length > 0) {
      document.getElementById('customDropdown').style.display = 'block';
    }
  }

  let searchDebounce = null;
  function cariAset() {
    const query = document.getElementById('searchInput').value.trim();
    document.getElementById('clearSearchBtn').style.display = query.length > 0 ? 'block' : 'none';
    
    if (query === '') {
      document.getElementById('customDropdown').style.display = 'none';
      return;
    }

    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(async () => {
      try {
        const res = await fetch(`/api/assets/search?kategori=INTERNAL&query=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderDropdownList(json.data || []);
      } catch (err) {
        console.error("Search error:", err);
      }
    }, 200);
  }

  function renderDropdownList(items) {
    const box = document.getElementById('customDropdown');
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
      
      html += `<div class="dropdown-item" onclick="pilihAset('${safeId}', '${safeDesc}', '${safeSn}', ${item.qty})">
        <strong>${item.id} ${snLabel}</strong>
        <span>${item.desc}</span>
      </div>`;
    });
    box.innerHTML = html;
  }

  function pilihAset(id, desc, sn, qty) {
    document.getElementById('searchInput').value = `${id} - ${desc}`;
    document.getElementById('assetNo').value = id;
    document.getElementById('assetDesc').value = desc;
    document.getElementById('assetSn').value = sn;
    document.getElementById('qtyBuku').value = qty;
    document.getElementById('clearSearchBtn').style.display = 'block';
    document.getElementById('customDropdown').style.display = 'none';
    document.getElementById('qtyFisik').focus();
  }

  function resetPencarian() {
    ['searchInput', 'assetNo', 'assetDesc', 'assetSn', 'qtyBuku'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('clearSearchBtn').style.display = 'none';
    document.getElementById('customDropdown').style.display = 'none';
    document.getElementById('searchInput').focus();
  }

  function previewUploadFoto(event, previewId, nameId, boxId, type) {
    const file = event.target.files[0];
    if (!file) return;

    document.getElementById(nameId).innerHTML = `OK <i class="fa-solid fa-check"></i><br><span style="font-size:10px;color:#27ae60;">(Ketuk ganti)</span>`;
    document.getElementById(boxId).style.borderColor = "var(--main-blue)";

    const reader = new FileReader();
    reader.onload = function (e) {
      const img = new Image();
      img.onload = function () {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const maxW = 800;
        let w = img.width, h = img.height;
        if (w > maxW) {
          h = Math.round((h * maxW) / w);
          w = maxW;
        }
        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(img, 0, 0, w, h);
        const compressedData = canvas.toDataURL('image/jpeg', 0.8);
        
        if (type === 'fisik') photoFisikBase64 = compressedData;
        else photoTaggingBase64 = compressedData;

        const previewEl = document.getElementById(previewId);
        previewEl.src = compressedData;
        previewEl.style.display = 'block';
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  async function submitOpnameInternal() {
    const assetNo = document.getElementById('assetNo').value;
    const desc = document.getElementById('assetDesc').value;
    const sn = document.getElementById('assetSn').value;
    const qtyBuku = document.getElementById('qtyBuku').value;
    const qtyFisik = document.getElementById('qtyFisik').value.trim();
    const tagging = document.getElementById('tagging').value;
    const status = document.getElementById('status').value;
    const kondisi = document.getElementById('kondisi').value;
    const lokasi = document.getElementById('lokasi').value.trim();

    let errs = [];
    if (!assetNo) errs.push("• Aset belum dipilih dari pencarian.");
    if (qtyFisik === "") errs.push("• Qty Fisik Aktual wajib diisi.");
    if (!tagging) errs.push("• Status Tagging belum dipilih.");
    if (!status) errs.push("• Status Penggunaan belum dipilih.");
    if (!kondisi) errs.push("• Kondisi Fisik belum dipilih.");
    if (!lokasi) errs.push("• Detail Area / Ruangan Internal wajib diisi.");
    if (!photoFisikBase64) errs.push("• Foto Aset (Fisik) wajib diambil.");
    if (!photoTaggingBase64) errs.push("• Foto Label (Tagging) wajib diambil.");

    if (errs.length > 0) {
      return showModal('error', 'Data Belum Lengkap', errs.join('<br>'), 'left');
    }

    showLoading(true);

    try {
      const response = await fetch("{{ route('opname.internal.store') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          nomor_asset: assetNo,
          deskripsi_asset: desc,
          serial_number: sn,
          qty_buku: qtyBuku,
          qty_fisik: qtyFisik,
          tagging: tagging,
          status_penggunaan: status,
          kondisi: kondisi,
          lokasi: lokasi,
          foto_fisik: photoFisikBase64,
          foto_tagging: photoTaggingBase64,
        })
      });

      const result = await response.json();

      if (response.ok && result.success) {
        showModal('success', 'Kerja Bagus!', `Data Stock Opname Internal aset <b>${assetNo}</b> berhasil tersimpan ke sistem.`, 'center', () => {
          window.location.reload();
        });
      } else {
        throw new Error(result.message || "Gagal menyimpan data.");
      }
    } catch (err) {
      showModal('error', 'Sistem Gagal', err.message);
    } finally {
      showLoading(false);
    }
  }

  // Tutup dropdown saat klik di luar
  document.addEventListener('click', function (e) {
    if (e.target.id !== 'searchInput' && e.target.id !== 'customDropdown') {
      const cd = document.getElementById('customDropdown');
      if (cd) cd.style.display = 'none';
    }
  });
</script>
@endpush
