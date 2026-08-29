@extends('layouts.app')

@section('title', 'Fixed Asset List')

@section('content')
<div id="viewMasterAsset" class="view-section active">
  @include('partials.header', ['title' => 'Fixed Asset List'])

  <div class="form-content">
    <!-- Filter Kategori & Limit -->
    <div class="grid-2" style="margin-bottom: 10px;">
      <div class="form-group" style="margin-bottom: 5px;">
        <label style="font-size: 11px;">Kategori Database</label>
        <select id="masterAssetKategori" class="form-control" onchange="loadMasterData(1)">
          <option value="INTERNAL" {{ $kategori === 'INTERNAL' ? 'selected' : '' }}>🏭 Internal Database</option>
          <option value="EXTERNAL" {{ $kategori === 'EXTERNAL' ? 'selected' : '' }}>🚚 External Database</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 5px;">
        <label style="font-size: 11px;">Baris per Halaman</label>
        <select id="limitMasterAsset" class="form-control" onchange="loadMasterData(1)">
          <option value="25">25 Aset</option>
          <option value="50" selected>50 Aset</option>
          <option value="100">100 Aset</option>
        </select>
      </div>
    </div>

    <!-- Pencarian Cepat -->
    <div class="form-group" style="margin-bottom: 12px;">
      <div class="input-wrapper">
        <i class="fa-solid fa-magnifying-glass icon-left"></i>
        <input type="text" id="searchMasterAsset" class="form-control" placeholder="Cari No Aset / Nama / Serial Number..." autocomplete="off" onkeyup="debounceSearch()">
      </div>
    </div>

    <!-- Container Hasil List Aset -->
    <div id="hasilMasterAsset" style="max-height: 480px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px; background: #fff;">
      <div style="text-align:center; padding: 30px; color:var(--main-blue);">
        <i class="fa-solid fa-spinner fa-spin fa-2x"></i><br><br>Memuat data aset...
      </div>
    </div>

    <!-- Pagination -->
    <div id="paginationMasterAsset" style="margin-top: 15px;"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let currentPage = 1;
  let searchTimer = null;

  document.addEventListener('DOMContentLoaded', function () {
    loadMasterData(1);
  });

  function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      loadMasterData(1);
    }, 300);
  }

  async function loadMasterData(page = 1) {
    currentPage = page;
    const kategori = document.getElementById('masterAssetKategori').value;
    const limit = document.getElementById('limitMasterAsset').value;
    const search = document.getElementById('searchMasterAsset').value.trim();
    const container = document.getElementById('hasilMasterAsset');
    const pagContainer = document.getElementById('paginationMasterAsset');

    container.innerHTML = '<div style="text-align:center; padding: 30px; color:var(--main-blue);"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><br><br>Memuat data aset...</div>';

    try {
      const url = `/assets?kategori=${kategori}&limit=${limit}&search=${encodeURIComponent(search)}&page=${page}`;
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });
      const json = await res.json();

      if (!json.success || !json.data || json.data.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding: 30px; color:var(--text-muted); font-size:13px;">Tidak ada data aset yang sesuai.</div>';
        pagContainer.innerHTML = '';
        return;
      }

      const total = json.pagination.total;
      const from = (json.pagination.current_page - 1) * json.pagination.per_page + 1;
      const to = Math.min(from + json.data.length - 1, total);

      let headerHtml = `<div style="font-size:11px; background:var(--main-blue-light); color:var(--main-blue); padding:8px 12px; font-weight:600; position:sticky; top:0; z-index:5; border-bottom: 1px solid var(--border-color);">
        Menampilkan ${from} - ${to} dari total ${formatRibuan(total)} aset
      </div>`;

      let listHtml = '';
      json.data.forEach(item => {
        const no = item.nomor_asset;
        const desc = item.deskripsi_asset;
        const sn = item.serial_number || '-';
        const qty = item.qty_buku || 0;
        const cap = item.cap_date ? item.cap_date.substring(0, 10) : '-';
        const nbv = item.nbv ? formatRibuan(item.nbv) : '0';
        const np = item.nilai_perolehan ? formatRibuan(item.nilai_perolehan) : '0';
        const ad = item.akumulasi_depresiasi ? formatRibuan(item.akumulasi_depresiasi) : '0';
        const costCenter = item.cost_center || '-';
        const alloc = item.allocation || '-';

        const snBadge = (sn && sn !== '-') ? `<span class="sn-badge" style="background:#27ae60;">SN: ${sn}</span>` : '';

        listHtml += `
          <div class="asset-list-item" onclick="detailAset('${no}', '${desc.replace(/'/g, "\\'")}', '${sn.replace(/'/g, "\\'")}', ${qty}, '${cap}', '${nbv}', '${np}', '${ad}', '${costCenter}', '${alloc}')">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3px;">
              <div style="font-size: 13px; font-weight: 600; color: var(--main-blue);">${no}</div>
              <i class="fa-solid fa-chevron-right" style="color:var(--text-muted); font-size: 10px; margin-top:3px;"></i>
            </div>
            <div style="font-size: 11px; margin-bottom: 4px; color: var(--text-main); line-height:1.3;">${desc}</div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div>${snBadge}</div>
              <span style="font-size:11px; color:var(--text-muted);"><i class="fa-solid fa-book"></i> Qty Buku: <strong>${qty}</strong></span>
            </div>
          </div>
        `;
      });

      container.innerHTML = headerHtml + listHtml;

      // Render Pagination
      renderPagination(json.pagination);

    } catch (err) {
      container.innerHTML = `<div class="alert-box alert-danger" style="display:block;">Gagal memuat data: ${err.message}</div>`;
      pagContainer.innerHTML = '';
    }
  }

  function renderPagination(p) {
    const pagContainer = document.getElementById('paginationMasterAsset');
    if (p.last_page <= 1) {
      pagContainer.innerHTML = '';
      return;
    }

    let html = `<div class="pagination">
      <button class="page-btn" ${p.current_page === 1 ? 'disabled' : ''} onclick="loadMasterData(${p.current_page - 1})">
        <i class="fa-solid fa-chevron-left"></i>
      </button>`;

    let start = Math.max(1, p.current_page - 2);
    let end = Math.min(p.last_page, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    if (start > 1) {
      html += `<button class="page-btn" onclick="loadMasterData(1)">1</button>`;
      if (start > 2) html += `<span style="font-size:12px; color:var(--text-muted);">...</span>`;
    }

    for (let i = start; i <= end; i++) {
      html += `<button class="page-btn ${i === p.current_page ? 'active' : ''}" onclick="loadMasterData(${i})">${i}</button>`;
    }

    if (end < p.last_page) {
      if (end < p.last_page - 1) html += `<span style="font-size:12px; color:var(--text-muted);">...</span>`;
      html += `<button class="page-btn" onclick="loadMasterData(${p.last_page})">${p.last_page}</button>`;
    }

    html += `<button class="page-btn" ${p.current_page === p.last_page ? 'disabled' : ''} onclick="loadMasterData(${p.current_page + 1})">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>`;

    pagContainer.innerHTML = html;
  }

  function detailAset(no, desc, sn, qty, cap, nbv, np, ad, cc, alloc) {
    showModal('info', 'Detail Master Aset', `
      <div style="font-size: 13px; line-height: 1.8; color: var(--text-main);">
        <table style="width:100%; border-collapse: collapse;">
          <tr><td style="padding: 4px 0; color: var(--text-muted); width: 42%;">Nomor Aset</td><td style="padding: 4px 0; font-weight:600;">: ${no}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted); vertical-align:top;">Deskripsi</td><td style="padding: 4px 0; font-weight:600; vertical-align:top;">: ${desc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Serial Number</td><td style="padding: 4px 0; font-weight:600;">: ${sn}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Cost Center</td><td style="padding: 4px 0; font-weight:600;">: ${cc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Alokasi</td><td style="padding: 4px 0; font-weight:600;">: ${alloc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Qty Buku</td><td style="padding: 4px 0; font-weight:600;">: ${qty} Unit</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Cap Date</td><td style="padding: 4px 0; font-weight:600;">: ${cap}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Nilai Perolehan</td><td style="padding: 4px 0; font-weight:600;">: Rp ${np}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">Akum. Depresiasi</td><td style="padding: 4px 0; font-weight:600;">: Rp ${ad}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--text-muted);">NBV (Net Book Value)</td><td style="padding: 4px 0; font-weight:600; color: #27ae60;">: Rp ${nbv}</td></tr>
        </table>
      </div>
    `, 'left');
  }
</script>
@endpush
