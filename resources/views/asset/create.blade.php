@extends('layouts.app')

@section('title', 'Fixed Asset Addition')

@section('content')
<div id="viewAddAsset" class="view-section active">
  @include('partials.header', ['title' => 'Fixed Asset Addition'])

  <div class="form-content">
    <!-- 1. Mass Addition Excel Section (Backend Processed) -->
    <div style="background: var(--main-blue-light); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #cce0f0;">
      <h4 style="font-size: 13px; color: var(--main-blue); margin-bottom: 8px; font-weight: 600;">
        <i class="fa-solid fa-file-excel" style="color:#27ae60;"></i> Tambah Aset Masal (Excel / CSV Backend Upload)
      </h4>
      <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
        Gunakan template resmi untuk menambahkan banyak aset sekaligus. Data diproses secara aman di backend server.
      </p>

      <div class="grid-2" style="gap: 10px;">
        <a href="{{ route('asset.template', 'addition') }}" class="btn-primary" style="background:#27ae60; font-size:11px; padding:8px 5px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-download" style="margin-right:4px;"></i> Unduh Template
        </a>

        <label class="btn-primary" style="background:var(--main-yellow); color:var(--main-blue); font-size:11px; padding:8px 5px; cursor:pointer; text-align:center; display:flex; align-items:center; justify-content:center; margin:0;">
          <i class="fa-solid fa-file-arrow-up" style="margin-right:4px;"></i> Upload File
          <input type="file" id="fileMassAdd" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleMassAdditionUpload(event)">
        </label>
      </div>
    </div>

    <div style="text-align: center; margin: 15px 0 10px; position: relative;">
      <hr style="border: 0; border-top: 1px solid var(--border-color);">
      <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 10px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
        ATAU INPUT MANUAL
      </span>
    </div>

    <!-- 2. Single Asset Addition Form -->
    <form id="formAddAsset" action="{{ route('asset.store') }}" method="POST">
      @csrf

      <div class="form-group">
        <label>Kategori Database <span style="color:red">*</span></label>
        <select id="addKategori" name="kategori_db" class="form-control" required>
          <option value="" disabled selected>Pilih Kategori Database...</option>
          <option value="INTERNAL">🏭 Internal Database (Pabrik / Kantor)</option>
          <option value="EXTERNAL">🚚 External Database (Vendor / Distributor)</option>
        </select>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Nomor Aset <span style="color:red">*</span></label>
          <input type="text" id="addNo" name="nomor_asset" class="form-control" placeholder="Contoh: 10001234" required>
        </div>
        <div class="form-group">
          <label>Serial Number <span style="color:red">*</span></label>
          <input type="text" id="addSn" name="serial_number" class="form-control" placeholder="Ketik strip '-' jika tidak ada" required>
        </div>
      </div>

      <div class="form-group">
        <label>Deskripsi Aset <span style="color:red">*</span></label>
        <textarea id="addDesc" name="deskripsi_asset" class="form-control" rows="2" placeholder="Nama / Deskripsi lengkap aset..." required></textarea>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Cost Center <span style="color:red">*</span></label>
          <input type="text" id="addCc" name="cost_center" class="form-control" placeholder="Contoh: CC-LOG-01" required>
        </div>
        <div class="form-group">
          <label>Qty Buku <span style="color:red">*</span></label>
          <input type="number" id="addQty" name="qty_buku" class="form-control" placeholder="Jumlah unit..." min="1" required>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Cap Date <span style="color:red">*</span></label>
          <input type="date" id="addCap" name="cap_date" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Alokasi / Lokasi <span style="color:red">*</span></label>
          <input type="text" id="addAlloc" name="allocation" class="form-control" placeholder="Contoh: Palembang" required>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Nilai Perolehan (Rp)</label>
          <input type="text" id="addNilai" class="form-control" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBV();">
          <input type="hidden" id="rawNilai" name="nilai_perolehan" value="0">
        </div>
        <div class="form-group">
          <label>Akum. Depresiasi (Rp)</label>
          <input type="text" id="addDepresiasi" class="form-control" placeholder="0" onkeyup="formatLiveRupiah(this); hitungNBV();">
          <input type="hidden" id="rawDepresiasi" name="akumulasi_depresiasi" value="0">
        </div>
      </div>

      <div class="form-group">
        <label>Net Book Value / NBV (Rp)</label>
        <input type="text" id="addNbv" class="form-control" readonly placeholder="0" style="background:#f8f9fa; font-weight:600; color:#27ae60;">
        <input type="hidden" id="rawNbv" name="nbv" value="0">
      </div>

      <button type="button" id="btnAddSubmit" class="btn-primary" onclick="submitSingleAsset()">
        <i class="fa-solid fa-plus-circle"></i> Simpan Aset ke Database
      </button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function hitungNBV() {
    const rawNp = document.getElementById('addNilai').value.replace(/[^0-9]/g, '');
    const rawAd = document.getElementById('addDepresiasi').value.replace(/[^0-9]/g, '');
    
    const np = Number(rawNp) || 0;
    const ad = Number(rawAd) || 0;
    const nbv = np - ad;

    document.getElementById('rawNilai').value = np;
    document.getElementById('rawDepresiasi').value = ad;
    document.getElementById('rawNbv').value = nbv;
    document.getElementById('addNbv').value = formatRibuan(nbv);
  }

  async function submitSingleAsset() {
    const kat = document.getElementById('addKategori').value;
    const no = document.getElementById('addNo').value.trim();
    const desc = document.getElementById('addDesc').value.trim();
    const sn = document.getElementById('addSn').value.trim();
    const cc = document.getElementById('addCc').value.trim();
    const qty = document.getElementById('addQty').value.trim();
    const cap = document.getElementById('addCap').value;
    const alloc = document.getElementById('addAlloc').value.trim();
    const np = Number(document.getElementById('rawNilai').value) || 0;
    const ad = Number(document.getElementById('rawDepresiasi').value) || 0;
    const nbv = Number(document.getElementById('rawNbv').value) || 0;

    let errs = [];
    if (!kat) errs.push("• Kategori Database belum dipilih.");
    if (!no) errs.push("• Nomor Aset wajib diisi.");
    if (!desc) errs.push("• Deskripsi Aset wajib diisi.");
    if (!sn) errs.push("• Serial Number wajib diisi (Ketik strip '-' jika tidak ada).");
    if (!cc) errs.push("• Cost Center wajib diisi.");
    if (!qty || Number(qty) < 1) errs.push("• Qty Buku minimal 1.");
    if (!cap) errs.push("• Cap Date wajib dipilih.");
    if (!alloc) errs.push("• Alokasi wajib diisi.");

    if (errs.length > 0) {
      return showModal('error', 'Data Belum Lengkap', errs.join('<br>'), 'left');
    }

    showLoading(true);

    try {
      const res = await fetch("{{ route('asset.store') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          kategori_db: kat,
          nomor_asset: no,
          deskripsi_asset: desc,
          serial_number: sn,
          cost_center: cc,
          qty_buku: qty,
          cap_date: cap,
          allocation: alloc,
          nilai_perolehan: np,
          akumulasi_depresiasi: ad,
          nbv: nbv,
        })
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Kerja Bagus!', json.message, 'center', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        throw new Error(json.message || "Gagal menyimpan aset baru.");
      }
    } catch (err) {
      showModal('error', 'Gagal Tambah Aset', err.message);
    } finally {
      showLoading(false);
    }
  }

  async function handleMassAdditionUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    showLoading(true);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch("{{ route('asset.mass_addition') }}", {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: formData
      });

      const json = await res.json();

      if (res.ok && json.success) {
        showModal('success', 'Upload Masal Berhasil!', json.message, 'center', () => {
          window.location.href = "{{ route('asset.index') }}";
        });
      } else {
        throw new Error(json.message || "Gagal memproses file upload.");
      }
    } catch (err) {
      showModal('error', 'Gagal Upload Masal', err.message);
    } finally {
      document.getElementById('fileMassAdd').value = '';
      showLoading(false);
    }
  }
</script>
@endpush
