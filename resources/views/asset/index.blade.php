@extends('layouts.app')

@section('title', 'Master Asset Database')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h2 class="card-title-text">
          <i class="fa-solid fa-server" style="color: var(--primary-600);"></i> Master Database Fixed Assets
        </h2>
        <p class="card-subtitle-text">Database inventaris aset tetap perusahaan (Internal & Eksternal) beserta rincian nilai perolehan dan penyusutan</p>
      </div>

      <!-- Quick Action: Tambah / Import -->
      <div style="display: flex; gap: 10px;">
        <a href="{{ route('asset.create') }}" class="btn-enterprise btn-enterprise-primary">
          <i class="fa-solid fa-file-circle-plus"></i> Tambah / Import Aset
        </a>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; align-items: center; justify-content: space-between;">
      <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; flex: 1;">
        <!-- Kategori Switcher Tabs -->
        <div style="display: flex; background: var(--slate-100); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--slate-200);">
          <button type="button" id="tabInternal" class="btn-enterprise" style="padding: 6px 14px; font-size: 12.5px; background: #ffffff; color: var(--primary-700); box-shadow: var(--shadow-sm);" onclick="setKategori('INTERNAL')">
            <i class="fa-solid fa-industry"></i> Internal (Pabrik)
          </button>
          <button type="button" id="tabExternal" class="btn-enterprise" style="padding: 6px 14px; font-size: 12.5px; background: transparent; color: var(--slate-500); box-shadow: none;" onclick="setKategori('EXTERNAL')">
            <i class="fa-solid fa-truck-fast"></i> Eksternal (Agen & Distributor)
          </button>
          <input type="hidden" id="masterAssetKategori" value="{{ $kategori ?? 'INTERNAL' }}">
        </div>

        <!-- Limit Dropdown -->
        <div style="display: flex; align-items: center; gap: 6px;">
          <span style="font-size: 12px; color: var(--slate-500); font-weight: 600;">Tampilkan:</span>
          <select id="limitMasterAsset" class="form-control-modern" style="padding: 6px 10px; font-size: 12.5px; width: auto;" onchange="loadMasterData(1)">
            <option value="25">25 Aset</option>
            <option value="50" selected>50 Aset</option>
            <option value="100">100 Aset</option>
          </select>
        </div>
      </div>

      <!-- Live Search Bar -->
      <div style="min-width: 280px; flex: 0.8;">
        <div class="input-container">
          <i class="fa-solid fa-magnifying-glass input-icon-left"></i>
          <input type="text" id="searchMasterAsset" class="form-control-modern" placeholder="Cari No Aset / Nama / SN / Cost Center..." autocomplete="off" onkeyup="debounceSearch()">
        </div>
      </div>
    </div>

    <!-- Data Table Container -->
    <div id="hasilMasterAsset" class="table-responsive-box">
      <div style="text-align:center; padding: 40px; color: var(--primary-600);">
        <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
        <div style="margin-top: 10px; font-weight: 600;">Memuat data master aset...</div>
      </div>
    </div>

    <!-- Pagination Container -->
    <div id="paginationMasterAsset" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;"></div>
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

  function setKategori(kat) {
    document.getElementById('masterAssetKategori').value = kat;
    const tabInt = document.getElementById('tabInternal');
    const tabExt = document.getElementById('tabExternal');

    if (kat === 'INTERNAL') {
      tabInt.style.background = '#ffffff';
      tabInt.style.color = 'var(--primary-700)';
      tabInt.style.boxShadow = 'var(--shadow-sm)';
      tabExt.style.background = 'transparent';
      tabExt.style.color = 'var(--slate-500)';
      tabExt.style.boxShadow = 'none';
    } else {
      tabExt.style.background = '#ffffff';
      tabExt.style.color = 'var(--primary-700)';
      tabExt.style.boxShadow = 'var(--shadow-sm)';
      tabInt.style.background = 'transparent';
      tabInt.style.color = 'var(--slate-500)';
      tabInt.style.boxShadow = 'none';
    }

    loadMasterData(1);
  }

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

    container.innerHTML = '<div style="text-align:center; padding: 40px; color:var(--primary-600);"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><div style="margin-top:10px; font-weight:600;">Memuat data master aset...</div></div>';

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
        container.innerHTML = '<div style="text-align:center; padding: 40px; color:var(--slate-400); font-size:13.5px;"><i class="fa-solid fa-box-open" style="font-size:28px; margin-bottom:10px; display:block;"></i>Tidak ada data aset yang cocok dengan kriteria pencarian.</div>';
        pagContainer.innerHTML = '';
        return;
      }

      const total = json.pagination.total;
      const from = (json.pagination.current_page - 1) * json.pagination.per_page + 1;
      const to = Math.min(from + json.data.length - 1, total);

      let tableHtml = `
        <table class="enterprise-table">
          <thead>
            <tr>
              <th>Nomor Aset</th>
              <th>Deskripsi Aset</th>
              <th>Serial Number</th>
              <th>Cost Center</th>
              <th>Alokasi Lokasi</th>
              <th>Qty Buku</th>
              <th>NBV (Rupiah)</th>
              <th style="text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
      `;

      json.data.forEach(item => {
        const no = item.nomor_asset;
        const desc = item.deskripsi_asset || '-';
        const sn = item.serial_number || '-';
        const qty = item.qty_buku || 0;
        const cap = item.cap_date ? item.cap_date.substring(0, 10) : '-';
        const nbv = item.nbv ? formatRibuan(item.nbv) : '0';
        const np = item.nilai_perolehan ? formatRibuan(item.nilai_perolehan) : '0';
        const ad = item.akumulasi_depresiasi ? formatRibuan(item.akumulasi_depresiasi) : '0';
        const costCenter = item.cost_center || '-';
        const alloc = item.allocation || '-';

        tableHtml += `
          <tr>
            <td>
              <span style="font-weight: 700; color: var(--primary-700);">${no}</span>
            </td>
            <td>
              <div style="font-weight: 600; color: var(--slate-800); max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${desc}">
                ${desc}
              </div>
            </td>
            <td>
              ${sn !== '-' ? `<span class="badge-pill badge-info"><i class="fa-solid fa-barcode"></i> ${sn}</span>` : '<span style="color:var(--slate-400);">-</span>'}
            </td>
            <td>
              <span style="font-size: 12px; color: var(--slate-600);">${costCenter}</span>
            </td>
            <td>
              <span style="font-size: 12px; color: var(--slate-600); max-width: 180px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${alloc}">
                <i class="fa-solid fa-location-dot" style="color: var(--primary-500); margin-right: 4px;"></i>${alloc}
              </span>
            </td>
            <td>
              <span class="badge-pill badge-primary">${qty} Unit</span>
            </td>
            <td>
              <span style="font-weight: 700; color: var(--success-600);">Rp ${nbv}</span>
            </td>
            <td style="text-align: center;">
              <button type="button" class="btn-enterprise btn-enterprise-outline" style="padding: 5px 10px; font-size: 12px;" onclick="detailAset('${no}', '${desc.replace(/'/g, "\\'")}', '${sn.replace(/'/g, "\\'")}', ${qty}, '${cap}', '${nbv}', '${np}', '${ad}', '${costCenter.replace(/'/g, "\\'")}', '${alloc.replace(/'/g, "\\'")}')">
                <i class="fa-solid fa-circle-info"></i> Detail
              </button>
            </td>
          </tr>
        `;
      });

      tableHtml += `</tbody></table>`;
      container.innerHTML = tableHtml;

      // Render Pagination Info & Controls
      renderPagination(json.pagination, from, to, total);

    } catch (err) {
      container.innerHTML = `<div style="padding: 20px; color: var(--danger-500); text-align: center;">Gagal memuat data: ${err.message}</div>`;
      pagContainer.innerHTML = '';
    }
  }

  function renderPagination(p, from, to, total) {
    const pagContainer = document.getElementById('paginationMasterAsset');
    if (!pagContainer) return;

    let infoHtml = `<div style="font-size: 12.5px; color: var(--slate-500); font-weight: 600;">
      Menampilkan <strong>${from}</strong> - <strong>${to}</strong> dari total <strong>${formatRibuan(total)}</strong> aset
    </div>`;

    if (p.last_page <= 1) {
      pagContainer.innerHTML = infoHtml;
      return;
    }

    let buttonsHtml = `<div style="display: flex; gap: 4px; align-items: center;">
      <button class="btn-enterprise btn-enterprise-outline" style="padding: 6px 12px; font-size: 12px;" ${p.current_page === 1 ? 'disabled' : ''} onclick="loadMasterData(${p.current_page - 1})">
        <i class="fa-solid fa-chevron-left"></i> Prev
      </button>`;

    let start = Math.max(1, p.current_page - 2);
    let end = Math.min(p.last_page, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    if (start > 1) {
      buttonsHtml += `<button class="btn-enterprise btn-enterprise-outline" style="padding: 6px 10px; font-size: 12px;" onclick="loadMasterData(1)">1</button>`;
      if (start > 2) buttonsHtml += `<span style="font-size:12px; color:var(--slate-400);">...</span>`;
    }

    for (let i = start; i <= end; i++) {
      const activeStyle = (i === p.current_page) ? 'background: var(--primary-600); color: #fff; border-color: var(--primary-600);' : '';
      buttonsHtml += `<button class="btn-enterprise btn-enterprise-outline" style="padding: 6px 10px; font-size: 12px; ${activeStyle}" onclick="loadMasterData(${i})">${i}</button>`;
    }

    if (end < p.last_page) {
      if (end < p.last_page - 1) buttonsHtml += `<span style="font-size:12px; color:var(--slate-400);">...</span>`;
      buttonsHtml += `<button class="btn-enterprise btn-enterprise-outline" style="padding: 6px 10px; font-size: 12px;" onclick="loadMasterData(${p.last_page})">${p.last_page}</button>`;
    }

    buttonsHtml += `<button class="btn-enterprise btn-enterprise-outline" style="padding: 6px 12px; font-size: 12px;" ${p.current_page === p.last_page ? 'disabled' : ''} onclick="loadMasterData(${p.current_page + 1})">
        Next <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>`;

    pagContainer.innerHTML = infoHtml + buttonsHtml;
  }

  function detailAset(no, desc, sn, qty, cap, nbv, np, ad, cc, alloc) {
    showModal('info', 'Rincian Master Aset', `
      <div style="font-size: 13px; line-height: 1.8; color: var(--slate-700); text-align: left;">
        <div style="background: var(--primary-50); padding: 12px; border-radius: var(--radius-md); margin-bottom: 12px; border: 1px solid var(--primary-200);">
          <div style="font-size: 11px; font-weight: 700; color: var(--primary-600); text-transform: uppercase;">Nomor Register Aset</div>
          <div style="font-size: 16px; font-weight: 800; color: var(--primary-800);">${no}</div>
        </div>
        <table style="width:100%; border-collapse: collapse;">
          <tr><td style="padding: 4px 0; color: var(--slate-400); width: 42%;">Deskripsi Aset</td><td style="padding: 4px 0; font-weight:700; color: var(--slate-800);">${desc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Serial Number</td><td style="padding: 4px 0; font-weight:600;">${sn}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Cost Center</td><td style="padding: 4px 0; font-weight:600;">${cc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Alokasi Lokasi</td><td style="padding: 4px 0; font-weight:600;">${alloc}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Kuantitas Buku</td><td style="padding: 4px 0; font-weight:700; color: var(--primary-700);">${qty} Unit</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Capitalized Date</td><td style="padding: 4px 0; font-weight:600;">${cap}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Nilai Perolehan</td><td style="padding: 4px 0; font-weight:600;">Rp ${np}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Akum. Depresiasi</td><td style="padding: 4px 0; font-weight:600;">Rp ${ad}</td></tr>
          <tr><td style="padding: 4px 0; color: var(--slate-400);">Net Book Value (NBV)</td><td style="padding: 4px 0; font-weight:800; color: var(--success-600);">Rp ${nbv}</td></tr>
        </table>
      </div>
    `, 'left');
  }
</script>
@endpush
