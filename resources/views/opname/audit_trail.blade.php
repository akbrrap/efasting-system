@extends('layouts.app')

@section('title', 'Fixed Asset Audit Trail')

@section('content')
<div id="viewRiwayat" class="view-section active">
  @include('partials.header', ['title' => 'Fixed Asset Audit Trail'])

  <div class="form-content">
    <div style="background: var(--main-blue-light); padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: var(--main-blue);">
      <i class="fa-solid fa-clock-rotate-left"></i> <strong>Audit Trail & Log Jejak Aset</strong>
      <p style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
        Lacak seluruh riwayat inspeksi fisik & tagging aset dari waktu ke waktu.
      </p>
    </div>

    <!-- Pencarian Aset -->
    <div class="form-group">
      <label>Pilih Aset yang Akan Dilacak</label>
      <div class="input-wrapper">
        <i class="fa-solid fa-magnifying-glass icon-left"></i>
        <input type="text" id="searchAdmin" class="form-control" style="padding-right: 35px;"
          placeholder="Ketik No Aset / Nama / SN..." autocomplete="off" onkeyup="cariAsetAudit()" onfocus="bukaDropdownAudit()">
        <i class="fa-solid fa-circle-xmark icon-right" id="clearSearchAdminBtn" style="display:none; color:#e74c3c; font-size:18px; cursor:pointer;" onclick="resetPencarianAudit()"></i>
      </div>
      <div id="dropdownAdmin" class="dropdown-list" style="display:none;"></div>
      <input type="hidden" id="assetNoAdmin">
    </div>

    <!-- Container Hasil Riwayat Jejak Aset -->
    <div id="hasilRiwayat" style="margin-top: 20px;"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function bukaDropdownAudit() {
    const text = document.getElementById('searchAdmin').value.trim();
    if (text.length > 0) document.getElementById('dropdownAdmin').style.display = 'block';
  }

  let searchAuditTimer = null;
  function cariAsetAudit() {
    const query = document.getElementById('searchAdmin').value.trim();
    document.getElementById('clearSearchAdminBtn').style.display = query.length > 0 ? 'block' : 'none';

    if (query === '') {
      document.getElementById('dropdownAdmin').style.display = 'none';
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

    if (items.length === 0) {
      box.innerHTML = '<div class="dropdown-item" style="color:#e74c3c;text-align:center;">Aset tidak ditemukan</div>';
      return;
    }

    let html = '';
    items.forEach(item => {
      let safeId = item.id.replace(/'/g, "\\'");
      let safeDesc = item.desc.replace(/'/g, "\\'");
      let snLabel = item.sn && item.sn !== '-' ? `<span class="sn-badge">SN: ${item.sn}</span>` : '';
      
      html += `<div class="dropdown-item" onclick="pilihAsetAudit('${safeId}', '${safeDesc}')">
        <strong>${item.id} ${snLabel}</strong>
        <span>${item.desc}</span>
      </div>`;
    });
    box.innerHTML = html;
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
    document.getElementById('searchAdmin').focus();
  }

  async function tarikRiwayatAset(assetNo) {
    const container = document.getElementById('hasilRiwayat');
    container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--main-blue);"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br><br>Mencari jejak aset...</div>';

    try {
      const res = await fetch(`/api/audit-trail/history?nomor_asset=${encodeURIComponent(assetNo)}`);
      const json = await res.json();

      if (!json.success || !json.data || json.data.length === 0) {
        container.innerHTML = '<div class="alert-box alert-danger" style="display:block; margin-top:15px;"><i class="fa-solid fa-circle-info"></i> Belum ada riwayat stock opname untuk aset ini.</div>';
        return;
      }

      let html = `<h4 style="font-size:14px; margin-bottom:15px; color:var(--main-blue); border-bottom:2px solid var(--border-color); padding-bottom:5px;">
        Ditemukan ${json.total} Riwayat Opname
      </h4>`;

      json.data.forEach(item => {
        const safeFotoFisik = (item.fotoFisik || '').replace(/'/g, "\\'");
        const safeFotoTagging = (item.fotoTagging || '').replace(/'/g, "\\'");

        const fotoFisikBtn = item.fotoFisik 
          ? `<button type="button" class="btn-primary" style="padding:8px; font-size:11px;" onclick="bukaPreviewFoto('${safeFotoFisik}')"><i class="fa-solid fa-image"></i> Foto Fisik</button>` 
          : `<button type="button" class="btn-primary btn-modal-outline" style="padding:8px; font-size:11px;" disabled><i class="fa-solid fa-image"></i> Tidak Ada</button>`;

        const fotoTagBtn = item.fotoTagging 
          ? `<button type="button" class="btn-primary" style="padding:8px; font-size:11px; background:var(--main-yellow); color:var(--main-blue);" onclick="bukaPreviewFoto('${safeFotoTagging}')"><i class="fa-solid fa-qrcode"></i> Foto Tag</button>` 
          : `<button type="button" class="btn-primary btn-modal-outline" style="padding:8px; font-size:11px;" disabled><i class="fa-solid fa-qrcode"></i> Tidak Ada</button>`;

        html += `
          <div style="background:#fff; border:1px solid var(--border-color); border-radius:8px; padding:15px; margin-bottom:15px; box-shadow:0 4px 6px rgba(0,0,0,0.05); position:relative;">
            <div style="position:absolute; top:15px; right:15px; font-size:10px; background:var(--main-yellow); color:white; padding:3px 8px; border-radius:12px; font-weight:bold;">
              ${item.tipe.toUpperCase()}
            </div>
            <div style="font-size:12px; color:var(--text-muted); margin-bottom:10px;">
              <i class="fa-solid fa-calendar-days"></i> ${item.tanggalView}
            </div>
            <div style="font-size:13px; margin-bottom:5px;"><strong>Auditor:</strong> ${item.petugas}</div>
            <div style="font-size:13px; margin-bottom:5px;"><strong>Qty Fisik:</strong> ${item.qtyFisik} Unit</div>
            <div style="font-size:13px; margin-bottom:5px;"><strong>Kondisi:</strong> ${item.kondisi} (${item.status})</div>
            <div style="font-size:13px; margin-bottom:15px;"><strong>Lokasi:</strong> ${item.lokasi}</div>
            
            <div class="grid-2">
              ${fotoFisikBtn}
              ${fotoTagBtn}
            </div>
          </div>
        `;
      });

      container.innerHTML = html;

    } catch (err) {
      container.innerHTML = `<div class="alert-box alert-danger" style="display:block;">Gagal menarik data riwayat: ${err.message}</div>`;
    }
  }

  document.addEventListener('click', function (e) {
    if (e.target.id !== 'searchAdmin' && e.target.id !== 'dropdownAdmin') {
      const box = document.getElementById('dropdownAdmin');
      if (box) box.style.display = 'none';
    }
  });
</script>
@endpush
