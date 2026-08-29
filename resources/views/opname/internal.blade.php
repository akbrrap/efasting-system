@extends('layouts.app')

@section('title', 'Stock Opname Internal Assets')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-industry" style="color: var(--primary-600);"></i> Form Stock Opname Internal (Pabrik & Kantor)
        </h2>
        <p class="card-subtitle-text">Verifikasi fisik aset tetap milik internal perusahaan, catat kuantitas aktual, dan unggah foto dokumentasi</p>
      </div>
      <div style="display: flex; align-items: center; gap: 8px;">
        <span class="badge-pill badge-primary">
          <i class="fa-solid fa-user-check"></i> {{ auth()->user()->nama_karyawan }}
        </span>
      </div>
    </div>

    <form id="formOpnameInternal" enctype="multipart/form-data">
      @csrf

      <!-- 1. Dokumentasi Foto (Fisik & Tagging) -->
      <div class="form-group-modern" style="margin-bottom: 24px;">
        <label class="form-label-modern">Dokumentasi Foto Sensus <span class="req">*</span></label>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <!-- Foto Fisik -->
          <div style="border: 2px dashed var(--primary-300); background: var(--primary-50); border-radius: var(--radius-lg); padding: 20px; text-align: center; cursor: pointer; position: relative; transition: all 0.2s ease;" id="boxFisik" onclick="document.getElementById('inputFotoFisik').click()">
            <input type="file" id="inputFotoFisik" accept="image/*" capture="environment" style="display: none;" onchange="previewUploadFoto(event, 'previewFisik', 'nameFisik', 'boxFisik', 'fisik')">
            <div id="iconFisikBox">
              <i class="fa-solid fa-camera" style="font-size: 28px; color: var(--primary-600); margin-bottom: 8px;"></i>
              <div id="nameFisik" style="font-weight: 700; font-size: 13px; color: var(--primary-700);">Ambil Foto Fisik Aset</div>
              <div style="font-size: 11.5px; color: var(--slate-500); margin-top: 2px;">Kamera / Galeri</div>
            </div>
            <img id="previewFisik" style="display: none; max-height: 140px; border-radius: 8px; margin: 0 auto; box-shadow: var(--shadow-md); object-fit: contain;" alt="Foto Fisik">
          </div>

          <!-- Foto Tagging -->
          <div style="border: 2px dashed #fcd34d; background: var(--accent-light); border-radius: var(--radius-lg); padding: 20px; text-align: center; cursor: pointer; position: relative; transition: all 0.2s ease;" id="boxTagging" onclick="document.getElementById('inputFotoTagging').click()">
            <input type="file" id="inputFotoTagging" accept="image/*" capture="environment" style="display: none;" onchange="previewUploadFoto(event, 'previewTagging', 'nameTagging', 'boxTagging', 'tagging')">
            <div id="iconTaggingBox">
              <i class="fa-solid fa-qrcode" style="font-size: 28px; color: var(--accent-600); margin-bottom: 8px;"></i>
              <div id="nameTagging" style="font-weight: 700; font-size: 13px; color: var(--accent-600);">Ambil Foto Tagging Barcode</div>
              <div style="font-size: 11.5px; color: var(--slate-500); margin-top: 2px;">Label Aset</div>
            </div>
            <img id="previewTagging" style="display: none; max-height: 140px; border-radius: 8px; margin: 0 auto; box-shadow: var(--shadow-md); object-fit: contain;" alt="Foto Tagging">
          </div>
        </div>
      </div>

      <!-- 2. Pencarian & Autocomplete Aset -->
      <div class="form-group-modern">
        <label for="searchInput" class="form-label-modern">Cari Nomor Aset / Deskripsi / Serial Number <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
          <input type="text" id="searchInput" class="form-control-modern" placeholder="Ketik nomor aset, nama mesin, atau serial number..." autocomplete="off" onkeyup="cariAset()" onfocus="bukaDropdown()">
          <i class="fa-solid fa-circle-xmark" id="clearSearchBtn" style="display:none; position: absolute; right: 14px; color: var(--danger-500); font-size: 16px; cursor: pointer;" onclick="resetPencarian()"></i>
        </div>
        <div id="customDropdown" class="search-dropdown-box"></div>
        <input type="hidden" id="assetNo" name="nomor_asset">
      </div>

      <!-- 3. Deskripsi & Detail Aset Terpilih -->
      <div class="form-group-modern">
        <label class="form-label-modern">Deskripsi Aset</label>
        <div class="input-container">
          <i class="fa-solid fa-box-archive input-icon-left"></i>
          <input type="text" id="assetDesc" name="deskripsi_asset" class="form-control-modern" readonly placeholder="Otomatis terisi saat aset dipilih...">
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group-modern">
          <label class="form-label-modern">Serial Number (SN)</label>
          <div class="input-container">
            <i class="fa-solid fa-barcode input-icon-left"></i>
            <input type="text" id="assetSn" name="serial_number" class="form-control-modern" readonly placeholder="-">
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Kuantitas Buku (Sistem)</label>
          <div class="input-container">
            <i class="fa-solid fa-book input-icon-left"></i>
            <input type="number" id="qtyBuku" name="qty_buku" class="form-control-modern" readonly placeholder="0">
          </div>
        </div>
      </div>

      <!-- 4. Input Kuantitas Fisik Aktual -->
      <div class="form-group-modern">
        <label for="qtyFisik" class="form-label-modern">Kuantitas Fisik Aktual di Lapangan <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-clipboard-check input-icon-left" style="color: var(--primary-600);"></i>
          <input type="number" id="qtyFisik" name="qty_fisik" class="form-control-modern" placeholder="Masukkan jumlah fisik yang dihitung..." required min="0">
        </div>
      </div>

      <!-- 5. Kondisi & Status Pemakaian -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
        <div class="form-group-modern">
          <label for="tagging" class="form-label-modern">Kelengkapan Tagging <span class="req">*</span></label>
          <select id="tagging" name="tagging" class="form-control-modern" required>
            <option value="" disabled selected>Pilih Tagging Label...</option>
            <option value="Ada">✅ Ada Label Tagging</option>
            <option value="Tidak Ada">❌ Tidak Ada Label</option>
          </select>
        </div>

        <div class="form-group-modern">
          <label for="status" class="form-label-modern">Status Penggunaan <span class="req">*</span></label>
          <select id="status" name="status_penggunaan" class="form-control-modern" required>
            <option value="" disabled selected>Pilih Status Pemakaian...</option>
            <option value="Digunakan">🟢 Sedang Digunakan</option>
            <option value="Idle Sementara">🟡 Idle Sementara</option>
            <option value="Idle Permanen">🔴 Idle Permanen</option>
          </select>
        </div>

        <div class="form-group-modern">
          <label for="kondisi" class="form-label-modern">Kondisi Fisik <span class="req">*</span></label>
          <select id="kondisi" name="kondisi" class="form-control-modern" required>
            <option value="" disabled selected>Pilih Kondisi Fisik...</option>
            <option value="Baik">✨ Kondisi Baik</option>
            <option value="Rusak">⚠️ Rusak / Butuh Perbaikan</option>
          </select>
        </div>
      </div>

      <!-- 6. Lokasi Aktual -->
      <div class="form-group-modern">
        <label for="lokasi" class="form-label-modern">Lokasi Fisik / Ruangan Aktual <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-location-dot input-icon-left"></i>
          <input type="text" id="lokasi" name="lokasi" class="form-control-modern" placeholder="Contoh: Gedung Packaging Lt 1, Ruang Server, dsb." required>
        </div>
      </div>

      <!-- Tombol Submit -->
      <div style="margin-top: 28px; display: flex; gap: 12px; justify-content: flex-end;">
        <button type="reset" class="btn-enterprise btn-enterprise-outline" onclick="resetForm()">
          <i class="fa-solid fa-rotate-left"></i> Reset Form
        </button>
        <button type="submit" id="btnSubmit" class="btn-enterprise btn-enterprise-primary" style="min-width: 180px;">
          <i class="fa-solid fa-paper-plane"></i> Simpan Hasil Opname
        </button>
      </div>
    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  let base64Fisik = null;
  let base64Tagging = null;
  let searchTimer = null;

  function previewUploadFoto(event, imgId, nameId, boxId, type) {
    const file = event.target.files[0];
    if (!file) return;

    const img = document.getElementById(imgId);
    const box = document.getElementById(boxId);
    const reader = new FileReader();

    reader.onload = function(e) {
      if (img) {
        img.src = e.target.result;
        img.style.display = 'block';
      }
      if (box) {
        const iconDiv = box.querySelector('div');
        if (iconDiv) iconDiv.style.display = 'none';
      }
      if (type === 'fisik') base64Fisik = e.target.result;
      if (type === 'tagging') base64Tagging = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  function cariAset() {
    clearTimeout(searchTimer);
    const query = document.getElementById('searchInput').value.trim();
    const dropdown = document.getElementById('customDropdown');
    const clearBtn = document.getElementById('clearSearchBtn');

    if (query.length > 0) {
      if (clearBtn) clearBtn.style.display = 'block';
    } else {
      if (clearBtn) clearBtn.style.display = 'none';
      if (dropdown) dropdown.style.display = 'none';
      return;
    }

    searchTimer = setTimeout(() => {
      fetch(`/api/assets/search?q=${encodeURIComponent(query)}&type=internal`)
        .then(res => res.json())
        .then(res => {
          if (!dropdown) return;
          dropdown.innerHTML = '';

          if (!res.data || res.data.length === 0) {
            dropdown.innerHTML = '<div style="padding: 12px 16px; font-size: 12.5px; color: var(--slate-400); text-align: center;">Tidak ada aset internal yang cocok.</div>';
            dropdown.style.display = 'block';
            return;
          }

          res.data.forEach(item => {
            const assetNo = item.nomor_asset || item.id || '';
            const assetDesc = item.deskripsi_asset || item.desc || '';
            const assetSn = item.serial_number || item.sn || '-';
            const assetQty = item.qty_buku !== undefined ? item.qty_buku : (item.qty || 0);
            const assetLoc = item.allocation || item.cost_center || '-';

            const row = document.createElement('div');
            row.className = 'dropdown-item-row';
            row.innerHTML = `
              <div class="dropdown-item-header">
                <i class="fa-solid fa-cube"></i> <strong>${assetNo}</strong> &bull; ${assetDesc}
              </div>
              <div class="dropdown-item-sub">
                SN: ${assetSn} | Qty Buku: ${assetQty} | Lokasi: ${assetLoc}
              </div>
            `;
            row.onclick = () => pilihAset(item);
            dropdown.appendChild(row);
          });

          dropdown.style.display = 'block';
        })
        .catch(err => {
          console.error(err);
        });
    }, 250);
  }

  function bukaDropdown() {
    const q = document.getElementById('searchInput').value.trim();
    if (q.length > 0) cariAset();
  }

  function pilihAset(item) {
    const assetNo = item.nomor_asset || item.id || '';
    const assetDesc = item.deskripsi_asset || item.desc || '';
    const assetSn = (item.serial_number && item.serial_number !== '-') ? item.serial_number : (item.sn && item.sn !== '-' ? item.sn : '');
    const assetQty = item.qty_buku !== undefined ? item.qty_buku : (item.qty || 0);
    const assetLoc = (item.allocation && item.allocation !== '-') ? item.allocation : (item.cost_center || '');

    document.getElementById('searchInput').value = `${assetNo} - ${assetDesc}`;
    document.getElementById('assetNo').value = assetNo;
    document.getElementById('assetDesc').value = assetDesc;
    document.getElementById('assetSn').value = assetSn;
    document.getElementById('qtyBuku').value = assetQty;
    document.getElementById('qtyFisik').value = assetQty || 1;
    document.getElementById('lokasi').value = assetLoc;

    const dropdown = document.getElementById('customDropdown');
    if (dropdown) dropdown.style.display = 'none';
  }

  function resetPencarian() {
    document.getElementById('searchInput').value = '';
    document.getElementById('assetNo').value = '';
    document.getElementById('assetDesc').value = '';
    document.getElementById('assetSn').value = '';
    document.getElementById('qtyBuku').value = '';
    document.getElementById('clearSearchBtn').style.display = 'none';
    document.getElementById('customDropdown').style.display = 'none';
  }

  function resetForm() {
    resetPencarian();
    base64Fisik = null;
    base64Tagging = null;
    document.getElementById('previewFisik').style.display = 'none';
    document.getElementById('previewTagging').style.display = 'none';
    document.getElementById('iconFisikBox').style.display = 'block';
    document.getElementById('iconTaggingBox').style.display = 'block';
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('customDropdown');
    const searchInput = document.getElementById('searchInput');
    if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
      dropdown.style.display = 'none';
    }
  });

  // Submit Handler
  document.getElementById('formOpnameInternal').addEventListener('submit', function(e) {
    e.preventDefault();

    const assetNo = document.getElementById('assetNo').value;
    if (!assetNo) {
      return showModal('error', 'Aset Belum Dipilih', 'Silakan pilih aset terlebih dahulu dari kotak pencarian autocomplete.');
    }

    const fileFisik = document.getElementById('inputFotoFisik').files[0];
    const fileTagging = document.getElementById('inputFotoTagging').files[0];

    if (!fileFisik && !base64Fisik) {
      return showModal('error', 'Foto Fisik Wajib', 'Silakan lampirkan foto fisik aset sebagai bukti sensus.');
    }

    showLoading(true);

    const formData = new FormData(this);
    if (base64Fisik && !fileFisik) formData.append('foto_fisik_base64', base64Fisik);
    if (base64Tagging && !fileTagging) formData.append('foto_tagging_base64', base64Tagging);

    fetch("{{ route('opname.internal.store') }}", {
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
      if (res.success || res.status === 'success') {
        showModal('success', 'Opname Berhasil Disimpan', res.message || 'Data sensus aset internal berhasil direkam ke dalam sistem.', 'center', () => {
          window.location.href = "{{ route('dashboard') }}";
        });
      } else {
        showModal('error', 'Gagal Menyimpan', res.message || 'Terjadi kendala saat memproses sensus.');
      }
    })
    .catch(err => {
      showLoading(false);
      console.error(err);
      showModal('error', 'Kesalahan Sistem', 'Tidak dapat terhubung ke server. Silakan coba kembali.');
    });
  });
</script>
@endpush
