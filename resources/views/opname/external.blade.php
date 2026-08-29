@extends('layouts.app')

@section('title', 'Stock Opname External Assets')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-truck-ramp-box" style="color: var(--accent-600);"></i> Form Stock Opname Eksternal (Vendor & Distributor)
        </h2>
        <p class="card-subtitle-text">Verifikasi fisik aset di lokasi pihak ketiga (Vendor, Distributor, Gudang Eksternal) beserta validasi kode entitas lokasi</p>
      </div>
      <div style="display: flex; align-items: center; gap: 8px;">
        <span class="badge-pill badge-warning">
          <i class="fa-solid fa-user-tag"></i> {{ auth()->user()->nama_karyawan }}
        </span>
      </div>
    </div>

    <form id="formOpnameExternal" enctype="multipart/form-data">
      @csrf

      <!-- 1. Dokumentasi Foto (Fisik & Tagging) -->
      <div class="form-group-modern" style="margin-bottom: 24px;">
        <label class="form-label-modern">Dokumentasi Foto Sensus Eksternal <span class="req">*</span></label>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <!-- Foto Fisik -->
          <div style="border: 2px dashed var(--primary-300); background: var(--primary-50); border-radius: var(--radius-lg); padding: 20px; text-align: center; cursor: pointer; position: relative; transition: all 0.2s ease;" id="boxFisik" onclick="document.getElementById('inputFotoFisik').click()">
            <input type="file" id="inputFotoFisik" accept="image/*" capture="environment" style="display: none;" onchange="previewUploadFoto(event, 'previewFisik', 'nameFisik', 'boxFisik', 'fisik')">
            <div id="iconFisikBox">
              <i class="fa-solid fa-camera" style="font-size: 28px; color: var(--primary-600); margin-bottom: 8px;"></i>
              <div id="nameFisik" style="font-weight: 700; font-size: 13px; color: var(--primary-700);">Foto Fisik Unit Eksternal</div>
              <div style="font-size: 11.5px; color: var(--slate-500); margin-top: 2px;">Kamera / Galeri</div>
            </div>
            <img id="previewFisik" style="display: none; max-height: 140px; border-radius: 8px; margin: 0 auto; box-shadow: var(--shadow-md); object-fit: contain;" alt="Foto Fisik">
          </div>

          <!-- Foto Tagging -->
          <div style="border: 2px dashed #fcd34d; background: var(--accent-light); border-radius: var(--radius-lg); padding: 20px; text-align: center; cursor: pointer; position: relative; transition: all 0.2s ease;" id="boxTagging" onclick="document.getElementById('inputFotoTagging').click()">
            <input type="file" id="inputFotoTagging" accept="image/*" capture="environment" style="display: none;" onchange="previewUploadFoto(event, 'previewTagging', 'nameTagging', 'boxTagging', 'tagging')">
            <div id="iconTaggingBox">
              <i class="fa-solid fa-qrcode" style="font-size: 28px; color: var(--accent-600); margin-bottom: 8px;"></i>
              <div id="nameTagging" style="font-weight: 700; font-size: 13px; color: var(--accent-600);">Foto Tagging Barcode</div>
              <div style="font-size: 11.5px; color: var(--slate-500); margin-top: 2px;">Label Aset</div>
            </div>
            <img id="previewTagging" style="display: none; max-height: 140px; border-radius: 8px; margin: 0 auto; box-shadow: var(--shadow-md); object-fit: contain;" alt="Foto Tagging">
          </div>
        </div>
      </div>

      <!-- 2. Pencarian & Autocomplete Aset Eksternal -->
      <div class="form-group-modern">
        <label for="searchInput" class="form-label-modern">Cari Aset Eksternal (No Aset / Deskripsi / SN) <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
          <input type="text" id="searchInput" class="form-control-modern" placeholder="Ketik nomor aset, nama kendaraan, atau serial number..." autocomplete="off" onkeyup="cariAset()" onfocus="bukaDropdown()">
          <i class="fa-solid fa-circle-xmark" id="clearSearchBtn" style="display:none; position: absolute; right: 14px; color: var(--danger-500); font-size: 16px; cursor: pointer;" onclick="resetPencarian()"></i>
        </div>
        <div id="customDropdown" class="search-dropdown-box"></div>
        <input type="hidden" id="assetNo" name="nomor_asset">
      </div>

      <!-- 3. Deskripsi & Serial Number -->
      <div class="form-group-modern">
        <label class="form-label-modern">Deskripsi Aset Eksternal</label>
        <div class="input-container">
          <i class="fa-solid fa-truck-moving input-icon-left"></i>
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
          <label class="form-label-modern">Kuantitas Buku</label>
          <div class="input-container">
            <i class="fa-solid fa-book input-icon-left"></i>
            <input type="number" id="qtyBuku" name="book_qty" class="form-control-modern" readonly placeholder="0">
          </div>
        </div>
      </div>

      <!-- 4. Pilihan Lokasi Aktual Eksternal (Entity Code) -->
      <div class="form-group-modern" style="background: var(--primary-50); padding: 18px; border-radius: var(--radius-lg); border: 1.5px solid var(--primary-200);">
        <label for="searchLokasi" class="form-label-modern" style="color: var(--primary-800); font-weight: 700;">
          <i class="fa-solid fa-location-crosshairs" style="color: var(--primary-600);"></i> Lokasi Aktual Eksternal Terdaftar <span class="req">*</span>
        </label>
        <div class="input-container" style="position: relative;">
          <i class="fa-solid fa-warehouse input-icon-left" style="color: var(--primary-600);"></i>
          <input type="text" id="searchLokasi" class="form-control-modern" placeholder="Pilih atau cari kode entitas lokasi (contoh: PLG-WH01)..." autocomplete="off" onkeyup="cariLokasi()" onfocus="bukaDropdownLokasi()" style="background: #ffffff;">
        </div>
        <div id="dropdownLokasi" class="search-dropdown-box"></div>
        <input type="hidden" id="kodeLokasiHidden" name="aktual_loc">
      </div>

      <!-- 5. Kuantitas Fisik Aktual -->
      <div class="form-group-modern">
        <label for="qtyFisik" class="form-label-modern">Kuantitas Fisik Aktual <span class="req">*</span></label>
        <div class="input-container">
          <i class="fa-solid fa-clipboard-check input-icon-left" style="color: var(--primary-600);"></i>
          <input type="number" id="qtyFisik" name="physic_qty" class="form-control-modern" placeholder="Masukkan jumlah fisik..." required min="0">
        </div>
      </div>

      <!-- 6. Tagging, Status & Kondisi -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px;">
        <div class="form-group-modern">
          <label for="tagging" class="form-label-modern">Kelengkapan Tagging <span class="req">*</span></label>
          <select id="tagging" name="kelengkapan_tagging" class="form-control-modern" required>
            <option value="" disabled selected>Pilih Tagging Label...</option>
            <option value="Ada">✅ Ada Label Tagging</option>
            <option value="Tidak Ada">❌ Tidak Ada Label</option>
          </select>
        </div>

        <div class="form-group-modern">
          <label for="status" class="form-label-modern">Status Penggunaan <span class="req">*</span></label>
          <select id="status" name="status" class="form-control-modern" required>
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

      <!-- 7. Keterangan / Catatan Tambahan -->
      <div class="form-group-modern">
        <label for="keterangan" class="form-label-modern">Catatan / Keterangan Auditor</label>
        <div class="input-container">
          <i class="fa-solid fa-pen-to-square input-icon-left"></i>
          <input type="text" id="keterangan" name="keterangan" class="form-control-modern" placeholder="Catatan opsional mengenai kondisi fisik aset...">
        </div>
      </div>

      <!-- Tombol Submit -->
      <div style="margin-top: 28px; display: flex; gap: 12px; justify-content: flex-end;">
        <button type="reset" class="btn-enterprise btn-enterprise-outline" onclick="resetForm()">
          <i class="fa-solid fa-rotate-left"></i> Reset Form
        </button>
        <button type="submit" id="btnSubmit" class="btn-enterprise btn-enterprise-yellow" style="min-width: 180px;">
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
  let searchLokasiTimer = null;

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
      fetch(`/api/assets/search?q=${encodeURIComponent(query)}&type=external`)
        .then(res => res.json())
        .then(res => {
          if (!dropdown) return;
          dropdown.innerHTML = '';

          if (!res.data || res.data.length === 0) {
            dropdown.innerHTML = '<div style="padding: 12px 16px; font-size: 12.5px; color: var(--slate-400); text-align: center;">Tidak ada aset eksternal yang cocok.</div>';
            dropdown.style.display = 'block';
            return;
          }

          res.data.forEach(item => {
            const row = document.createElement('div');
            row.className = 'dropdown-item-row';
            row.innerHTML = `
              <div class="dropdown-item-header">
                <i class="fa-solid fa-truck"></i> ${item.nomor_asset} &bull; ${item.deskripsi_asset}
              </div>
              <div class="dropdown-item-sub">
                SN: ${item.serial_number || '-'} | Qty Buku: ${item.qty_buku || 0} | Lokasi: ${item.allocation || '-'}
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
    document.getElementById('searchInput').value = `${item.nomor_asset} - ${item.deskripsi_asset}`;
    document.getElementById('assetNo').value = item.nomor_asset;
    document.getElementById('assetDesc').value = item.deskripsi_asset || '';
    document.getElementById('assetSn').value = item.serial_number || '';
    document.getElementById('qtyBuku').value = item.qty_buku || 0;
    document.getElementById('qtyFisik').value = item.qty_buku || 1;

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

  // Location Search Dropdown
  function cariLokasi() {
    clearTimeout(searchLokasiTimer);
    const query = document.getElementById('searchLokasi').value.trim();
    const dropdown = document.getElementById('dropdownLokasi');

    searchLokasiTimer = setTimeout(() => {
      fetch(`/api/lokasi/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(res => {
          if (!dropdown) return;
          dropdown.innerHTML = '';

          if (!res.data || res.data.length === 0) {
            dropdown.innerHTML = '<div style="padding: 12px 16px; font-size: 12.5px; color: var(--slate-400); text-align: center;">Tidak ada kode lokasi yang cocok.</div>';
            dropdown.style.display = 'block';
            return;
          }

          res.data.forEach(item => {
            const row = document.createElement('div');
            row.className = 'dropdown-item-row';
            row.innerHTML = `
              <div class="dropdown-item-header">
                <i class="fa-solid fa-location-dot"></i> ${item.code_entity}
              </div>
              <div class="dropdown-item-sub">${item.description}</div>
            `;
            row.onclick = () => {
              document.getElementById('searchLokasi').value = `${item.code_entity} - ${item.description}`;
              document.getElementById('kodeLokasiHidden').value = item.code_entity;
              dropdown.style.display = 'none';
            };
            dropdown.appendChild(row);
          });

          dropdown.style.display = 'block';
        })
        .catch(err => {
          console.error(err);
        });
    }, 250);
  }

  function bukaDropdownLokasi() {
    cariLokasi();
  }

  function resetForm() {
    resetPencarian();
    document.getElementById('searchLokasi').value = '';
    document.getElementById('kodeLokasiHidden').value = '';
    base64Fisik = null;
    base64Tagging = null;
    document.getElementById('previewFisik').style.display = 'none';
    document.getElementById('previewTagging').style.display = 'none';
    document.getElementById('iconFisikBox').style.display = 'block';
    document.getElementById('iconTaggingBox').style.display = 'block';
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    const dropdownAsset = document.getElementById('customDropdown');
    const searchAsset = document.getElementById('searchInput');
    if (dropdownAsset && !dropdownAsset.contains(e.target) && e.target !== searchAsset) {
      dropdownAsset.style.display = 'none';
    }

    const dropdownLokasi = document.getElementById('dropdownLokasi');
    const searchLokasi = document.getElementById('searchLokasi');
    if (dropdownLokasi && !dropdownLokasi.contains(e.target) && e.target !== searchLokasi) {
      dropdownLokasi.style.display = 'none';
    }
  });

  // Submit Handler
  document.getElementById('formOpnameExternal').addEventListener('submit', function(e) {
    e.preventDefault();

    const assetNo = document.getElementById('assetNo').value;
    if (!assetNo) {
      return showModal('error', 'Aset Belum Dipilih', 'Silakan pilih aset eksternal terlebih dahulu dari kotak pencarian autocomplete.');
    }

    const lokasi = document.getElementById('kodeLokasiHidden').value;
    if (!lokasi) {
      return showModal('error', 'Lokasi Wajib Dipilih', 'Silakan pilih kode entitas lokasi aktual eksternal dari daftar.');
    }

    const fileFisik = document.getElementById('inputFotoFisik').files[0];
    const fileTagging = document.getElementById('inputFotoTagging').files[0];

    if (!fileFisik && !base64Fisik) {
      return showModal('error', 'Foto Fisik Wajib', 'Silakan lampirkan foto fisik unit eksternal sebagai bukti sensus.');
    }

    showLoading(true);

    const formData = new FormData(this);
    if (base64Fisik && !fileFisik) formData.append('foto_fisik_base64', base64Fisik);
    if (base64Tagging && !fileTagging) formData.append('foto_tagging_base64', base64Tagging);

    fetch("{{ route('opname.external.store') }}", {
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
        showModal('success', 'Opname Eksternal Berhasil Disimpan', res.message || 'Data sensus aset eksternal berhasil direkam ke dalam sistem.', 'center', () => {
          window.location.href = "{{ route('dashboard') }}";
        });
      } else {
        showModal('error', 'Gagal Menyimpan', res.message || 'Terjadi kendala saat memproses sensus eksternal.');
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
