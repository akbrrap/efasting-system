@extends('layouts.app')

@section('title', 'Home Dashboard')

@section('content')
<div id="viewHome" class="view-section active">
  @include('partials.header', ['title' => 'Home Dashboard'])

  <div class="form-content" style="padding-top: 15px;">
    <div style="text-align: center; margin-bottom: 20px;">
      <h3 style="color: var(--main-blue); font-size: 18px; margin-bottom: 5px;">
        Selamat Datang, <span>{{ auth()->user()->nama_karyawan ?? 'Petugas' }}</span>!
      </h3>
      <p style="color: var(--text-muted); font-size: 13px;">
        Pencapaian Stock Opname bulan <strong style="color:var(--main-blue);">{{ $bulanTahunLabel }}</strong>
      </p>
    </div>

    <!-- Cards Total Aset Opname -->
    <div class="grid-2">
      <div class="dashboard-card internal">
        <div class="icon-circle"><i class="fa-solid fa-industry"></i></div>
        <h3>Internal Aset</h3>
        <div class="angka">{{ $totalInternal }}</div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
          dari {{ $masterInternalCount }} master aset ({{ $percentInternal }}%)
        </div>
      </div>

      <div class="dashboard-card external">
        <div class="icon-circle"><i class="fa-solid fa-truck-fast"></i></div>
        <h3>External Aset</h3>
        <div class="angka">{{ $totalExternal }}</div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
          dari {{ $masterExternalCount }} master aset ({{ $percentExternal }}%)
        </div>
      </div>
    </div>

    <h4 style="font-size: 14px; margin: 20px 0 12px; color: var(--main-blue); border-bottom: 2px solid var(--border-color); padding-bottom: 5px;">
      <i class="fa-solid fa-chart-pie" style="margin-right: 5px;"></i> Analytics Data Aset Bulan Ini
    </h4>

    <!-- 3 Doughnut Charts -->
    <div class="stat-container">
      <div class="stat-box">
        <h5><i class="fa-solid fa-tags" style="color:#f39c12;"></i> Tagging Label</h5>
        <div class="chart-wrapper"><canvas id="chartTagging"></canvas></div>
      </div>

      <div class="stat-box">
        <h5><i class="fa-solid fa-screwdriver-wrench" style="color:#27ae60;"></i> Kondisi Fisik</h5>
        <div class="chart-wrapper"><canvas id="chartKondisi"></canvas></div>
      </div>

      <div class="stat-box" style="grid-column: span 1;">
        <h5><i class="fa-solid fa-arrows-spin" style="color:#2980b9;"></i> Status Pemakaian</h5>
        <div class="chart-wrapper"><canvas id="chartStatus"></canvas></div>
      </div>
    </div>

    <!-- Quick Action Button -->
    <div style="margin-top: 20px;">
      @if (auth()->user()->isExternal())
        <a href="{{ route('opname.external') }}" class="btn-primary" style="display:flex; justify-content:center; align-items:center; text-decoration:none;">
          <i class="fa-solid fa-play" style="margin-right: 8px;"></i> Mulai Opname Eksternal
        </a>
      @else
        <a href="{{ route('opname.internal') }}" class="btn-primary" style="display:flex; justify-content:center; align-items:center; text-decoration:none;">
          <i class="fa-solid fa-play" style="margin-right: 8px;"></i> Mulai Opname Sekarang
        </a>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tagAda = {{ $tagAda }};
    const tagTidak = {{ $tagTidak }};
    const konBaik = {{ $konBaik }};
    const konRusak = {{ $konRusak }};
    const statGuna = {{ $statGuna }};
    const statSem = {{ $statSem }};
    const statPerm = {{ $statPerm }};

    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      borderWidth: 0,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: { size: 11, family: 'Poppins' }
          }
        }
      }
    };

    // 1. Chart Tagging
    new Chart(document.getElementById('chartTagging').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Ada Label', 'Tidak Ada'],
        datasets: [{
          data: [tagAda, tagTidak],
          backgroundColor: ['#27ae60', '#e74c3c'],
          hoverOffset: 4
        }]
      },
      options: commonOptions
    });

    // 2. Chart Kondisi
    new Chart(document.getElementById('chartKondisi').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Baik', 'Rusak'],
        datasets: [{
          data: [konBaik, konRusak],
          backgroundColor: ['#27ae60', '#e74c3c'],
          hoverOffset: 4
        }]
      },
      options: commonOptions
    });

    // 3. Chart Status Pemakaian
    new Chart(document.getElementById('chartStatus').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Digunakan', 'Idle Sementara', 'Idle Permanen'],
        datasets: [{
          data: [statGuna, statSem, statPerm],
          backgroundColor: ['#2980b9', '#f39c12', '#e74c3c'],
          hoverOffset: 4
        }]
      },
      options: commonOptions
    });
  });
</script>
@endpush
