@extends('layouts.app')

@section('title', 'Fixed Asset Audit Trail')

@section('content')
<div style="max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary-600);"></i> Log Audit Trail & Jejak Sensus Aset
        </h2>
        <p class="card-subtitle-text">Lacak histori inspeksi berkala, kondisi fisik, catatan auditor, dan dokumentasi foto sensus aset tetap</p>
      </div>
    </div>

    <!-- Pencarian Aset -->
    <div class="form-group-modern">
      <label for="searchAdmin" class="form-label-modern">Pilih Nomor Aset / Deskripsi yang Ingin Dilacak Jejaknya</label>
      <div class="input-container">
        <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
        <input type="text" id="searchAdmin" class="form-control-modern" placeholder="Ketik No Aset / Nama / Serial Number..." autocomplete="off" onkeyup="cariAsetAudit()" onfocus="bukaDropdownAudit()">
        <i class="fa-solid fa-circle-xmark" id="clearSearchAdminBtn" style="display:none; position: absolute; right: 14px; color: var(--danger-500); font-size: 16px; cursor: pointer;" onclick="resetPencarianAudit()"></i>
      </div>
      <div id="dropdownAdmin" class="search-dropdown-box"></div>
      <input type="hidden" id="assetNoAdmin">
    </div>

    <!-- Container Hasil Riwayat Jejak Aset -->
    <div id="hasilRiwayat" style="margin-top: 24px;"></div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  function bukaDropdownAudit() {
    const text = document.getElementById('searchAdmin').value.trim();
    if (text.length > 0) cariAsetAudit();
  }

  let searchAuditTimer = null;
  function cariAsetAudit() {
    const query = document.getElementById('searchAdmin').value.trim();
    const clearBtn = document.getElementById('clearSearchAdminBtn');
    const dropdown = document.getElementById('dropdownAdmin');

    if (query.length > 0) {
      if (clearBtn) clearBtn.style.display = 'block';
    } else {
      if (clearBtn) clearBtn.style.display = 'none';
      if (dropdown) dropdown.style.display = 'none';
      return;
    }

    clearTimeout(searchAuditTimer);
    searchAuditTimer = setTimeout(async () => {
      try {
        const res = await fetch(`/api/assets/search?kategori=ALL&query=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderDropdownAudit(json.data || []);
      } catch (err) {
        console.error("Search error:", err);
      }
    }, 200);
  }

  function renderDropdownAudit(items) {
    const box = document.getElementById('dropdownAdmin');
    box.style.display = 'block';
    box.innerHTML = '';

    if (items.length === 0) {
      box.innerHTML = '<div style="padding:12px 16px; font-size:12.5px; color:var(--slate-400); text-align:center;">Aset tidak ditemukan.</div>';
      return;
    }

    items.forEach(item => {
      let safeId = item.id.replace(/'/g, "\\'");
      let safeDesc = item.desc.replace(/'/g, "\\'");
      let snLabel = item.sn && item.sn !== '-' ? `<span class="badge-pill badge-info" style="font-size:10px;">SN: ${item.sn}</span>` : '';
      
      const row = document.createElement('div');
      row.className = 'dropdown-item-row';
      row.innerHTML = `
        <div class="dropdown-item-header">
          <i class="fa-solid fa-cube"></i> ${item.id} ${snLabel}
        </div>
        <div class="dropdown-item-sub">${item.desc}</div>
      `;
      row.onclick = () => pilihAsetAudit(safeId, safeDesc);
      box.appendChild(row);
    });
  }

  function pilihAsetAudit(id, desc) {
    document.getElementById('searchAdmin').value = `${id} - ${desc}`;
    document.getElementById('assetNoAdmin').value = id;
    document.getElementById('clearSearchAdminBtn').style.display = 'block';
    document.getElementById('dropdownAdmin').style.display = 'none';
    
    tarikRiwayatAset(id);
  }

  function resetPencarianAudit() {
    document.getElementById('searchAdmin').value = '';
    document.getElementById('assetNoAdmin').value = '';
    document.getElementById('hasilRiwayat').innerHTML = '';
    document.getElementById('clearSearchAdminBtn').style.display = 'none';
    document.getElementById('dropdownAdmin').style.display = 'none';
  }

  async function tarikRiwayatAset(nomorAsset) {
    const box = document.getElementById('hasilRiwayat');
    box.innerHTML = `
      <div style="text-align:center; padding: 40px; color: var(--primary-600);">
        <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
        <div style="margin-top: 10px; font-weight:600;">Mengambil timeline riwayat opname...</div>
      </div>
    `;

    try {
      const res = await fetch(`/api/audit-trail/history?nomor_asset=${encodeURIComponent(nomorAsset)}`);
      const json = await res.json();

      if (!json.success || !json.data || json.data.length === 0) {
        box.innerHTML = `
          <div style="text-align:center; padding: 30px; background: var(--slate-50); border: 1px dashed var(--slate-300); border-radius: var(--radius-lg); color: var(--slate-500);">
            <i class="fa-solid fa-folder-open" style="font-size: 28px; margin-bottom: 8px; display: block; color: var(--slate-400);"></i>
            Aset <strong>${nomorAsset}</strong> belum memiliki catatan riwayat sensus opname.
          </div>
        `;
        return;
      }

      let html = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid var(--slate-200);">
          <h4 style="font-size: 15px; font-weight: 800; color: var(--primary-800); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-timeline" style="color: var(--primary-600);"></i> Ditemukan ${json.total} Riwayat Sensus
          </h4>
          <span class="badge-pill badge-primary">No Aset: ${nomorAsset}</span>
        </div>
      `;

      json.data.forEach(item => {
        const safeFotoFisik = (item.fotoFisik || '').replace(/'/g, "\\'");
        const safeFotoTagging = (item.fotoTagging || '').replace(/'/g, "\\'");

        const fotoFisikBtn = item.fotoFisik 
          ? `<button type="button" class="btn-enterprise btn-enterprise-primary" style="padding: 6px 12px; font-size: 12px;" onclick="bukaPreviewFoto('${safeFotoFisik}')"><i class="fa-solid fa-image"></i> Foto Fisik</button>` 
          : `<button type="button" class="btn-enterprise btn-enterprise-outline" style="padding: 6px 12px; font-size: 12px;" disabled><i class="fa-solid fa-image"></i> Tidak Ada Foto</button>`;

        const fotoTagBtn = item.fotoTagging 
          ? `<button type="button" class="btn-enterprise btn-enterprise-yellow" style="padding: 6px 12px; font-size: 12px;" onclick="bukaPreviewFoto('${safeFotoTagging}')"><i class="fa-solid fa-qrcode"></i> Foto Tagging</button>` 
          : `<button type="button" class="btn-enterprise btn-enterprise-outline" style="padding: 6px 12px; font-size: 12px;" disabled><i class="fa-solid fa-qrcode"></i> Tidak Ada Tag</button>`;

        const isGood = item.kondisi && item.kondisi.toLowerCase().includes('baik');
        const badgeKondisi = isGood ? `<span class="badge-pill badge-success">Kondisi: ${item.kondisi}</span>` : `<span class="badge-pill badge-danger">Kondisi: ${item.kondisi}</span>`;

        html += `
          <div style="background: #ffffff; border: 1.5px solid var(--border-card); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 16px; box-shadow: var(--shadow-sm); position: relative; transition: all 0.2s ease;">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
              <div>
                <span class="badge-pill ${item.tipe === 'internal' ? 'badge-primary' : 'badge-warning'}" style="margin-right: 6px;">
                  ${item.tipe.toUpperCase()}
                </span>
                <span style="font-size: 12.5px; color: var(--slate-500); font-weight: 600;">
                  <i class="fa-solid fa-calendar-day" style="margin-right: 4px;"></i> ${item.tanggalView}
                </span>
              </div>

              <div>
                ${badgeKondisi}
              </div>
            </div>

            <!-- Detail Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 13px; color: var(--slate-700); background: var(--slate-50); padding: 14px; border-radius: var(--radius-md); margin-bottom: 14px;">
              <div><strong>Auditor:</strong> ${item.petugas || '-'}</div>
              <div><strong>Lokasi:</strong> ${item.lokasi || '-'}</div>
              <div><strong>Qty Buku:</strong> ${item.qtyBuku} Unit</div>
              <div><strong>Qty Fisik:</strong> <strong style="color:var(--primary-700);">${item.qtyFisik} Unit</strong> (Selisih: ${item.selisih})</div>
              <div><strong>Status:</strong> ${item.status || '-'}</div>
              <div><strong>Tagging:</strong> ${item.tagging || '-'}</div>
            </div>

            ${item.keterangan ? `<div style="font-size: 12.5px; color: var(--slate-600); margin-bottom: 14px; font-style: italic;"><i class="fa-solid fa-quote-left" style="color:var(--slate-400); margin-right:4px;"></i>${item.keterangan}</div>` : ''}

            <!-- Tombol Foto -->
            <div style="display: flex; gap: 10px; align-items: center;">
              ${fotoFisikBtn}
              ${fotoTagBtn}
            </div>

          </div>
        `;
      });

      box.innerHTML = html;

    } catch (err) {
      box.innerHTML = `<div style="padding: 20px; color: var(--danger-500); text-align: center;">Gagal memuat riwayat aset: ${err.message}</div>`;
    }
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('dropdownAdmin');
    const searchInput = document.getElementById('searchAdmin');
    if (dropdown && !dropdown.contains(e.target) && e.target !== searchInput) {
      dropdown.style.display = 'none';
    }
  });
</script>
@endpush
