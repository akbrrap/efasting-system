@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

  <!-- Welcome Hero Banner -->
  <div style="background: linear-gradient(135deg, var(--primary-800) 0%, var(--primary-900) 100%); border-radius: var(--radius-xl); padding: 28px 32px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
    <div style="position: absolute; right: -30px; top: -30px; width: 220px; height: 220px; background: rgba(59, 130, 246, 0.12); border-radius: 50%; filter: blur(50px);"></div>
    <div style="position: absolute; right: 80px; bottom: -30px; width: 160px; height: 160px; background: rgba(245, 158, 11, 0.15); border-radius: 50%; filter: blur(40px);"></div>

    <div style="position: relative; z-index: 1; max-width: 650px;">
      <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.12); padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 700; margin-bottom: 12px; color: var(--accent-500);">
        <i class="fa-solid fa-calendar-day"></i> Periode Audit: {{ $bulanTahunLabel }}
      </div>
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 6px; line-height: 1.2;">
        Selamat Datang, {{ auth()->user()->nama_karyawan ?? 'Petugas Opname' }}!
      </h2>
      <p style="font-size: 13.5px; color: var(--primary-200); line-height: 1.5;">
        Pantau progres sensus aset tetap, verifikasi fisik, status tagging barcode, dan analitik kondisi aset real-time di seluruh lokasi.
      </p>
    </div>

    <div style="position: relative; z-index: 1; display: flex; gap: 12px;">
      @if (auth()->user()->isExternal())
        <a href="{{ route('opname.external') }}" class="btn-enterprise btn-enterprise-yellow" style="padding: 12px 20px;">
          <i class="fa-solid fa-play"></i> Mulai Opname Eksternal
        </a>
      @else
        <a href="{{ route('opname.internal') }}" class="btn-enterprise btn-enterprise-yellow" style="padding: 12px 20px;">
          <i class="fa-solid fa-play"></i> Mulai Opname Internal
        </a>
      @endif
    </div>
  </div>

  <!-- KPI Summary Cards Grid -->
  <div class="stat-grid">
    <!-- 1. Internal Asset KPI -->
    <div class="stat-widget" style="--stat-color: var(--primary-600); --stat-bg: var(--primary-50);">
      <div class="stat-icon-box">
        <i class="fa-solid fa-industry"></i>
      </div>
      <div class="stat-body">
        <div class="stat-label">Internal Aset Ter-Opname</div>
        <div class="stat-number">{{ $totalInternal }} <span style="font-size: 14px; font-weight: 600; color: var(--slate-400);">/ {{ $masterInternalCount }}</span></div>
        <div style="width: 100%; height: 6px; background: var(--slate-100); border-radius: 10px; margin: 8px 0; overflow: hidden;">
          <div style="width: {{ min(100, $percentInternal) }}%; height: 100%; background: linear-gradient(90deg, var(--primary-600), var(--primary-400)); border-radius: 10px;"></div>
        </div>
        <div class="stat-sub">
          <i class="fa-solid fa-circle-check" style="color: var(--success-500);"></i> Progres Sensus: <strong>{{ $percentInternal }}%</strong>
        </div>
      </div>
    </div>

    <!-- 2. External Asset KPI -->
    <div class="stat-widget" style="--stat-color: var(--accent-600); --stat-bg: var(--accent-light);">
      <div class="stat-icon-box">
        <i class="fa-solid fa-truck-fast"></i>
      </div>
      <div class="stat-body">
        <div class="stat-label">Eksternal Aset Ter-Opname</div>
        <div class="stat-number">{{ $totalExternal }} <span style="font-size: 14px; font-weight: 600; color: var(--slate-400);">/ {{ $masterExternalCount }}</span></div>
        <div style="width: 100%; height: 6px; background: var(--slate-100); border-radius: 10px; margin: 8px 0; overflow: hidden;">
          <div style="width: {{ min(100, $percentExternal) }}%; height: 100%; background: linear-gradient(90deg, var(--accent-600), var(--accent-500)); border-radius: 10px;"></div>
        </div>
        <div class="stat-sub">
          <i class="fa-solid fa-circle-check" style="color: var(--accent-600);"></i> Progres Sensus: <strong>{{ $percentExternal }}%</strong>
        </div>
      </div>
    </div>

    <!-- 3. Total Master Assets -->
    <div class="stat-widget" style="--stat-color: var(--info-500); --stat-bg: var(--info-light);">
      <div class="stat-icon-box">
        <i class="fa-solid fa-database"></i>
      </div>
      <div class="stat-body">
        <div class="stat-label">Total Master Database</div>
        <div class="stat-number">{{ $masterInternalCount + $masterExternalCount }}</div>
        <div class="stat-sub" style="margin-top: 14px;">
          <i class="fa-solid fa-layer-group" style="color: var(--info-500);"></i> Master Internal & Eksternal
        </div>
      </div>
    </div>

    <!-- 4. Asset Health Condition -->
    <div class="stat-widget" style="--stat-color: var(--success-500); --stat-bg: var(--success-light);">
      <div class="stat-icon-box">
        <i class="fa-solid fa-heart-pulse"></i>
      </div>
      <div class="stat-body">
        <div class="stat-label">Kondisi Fisik Baik</div>
        <div class="stat-number">{{ $konBaik }} <span style="font-size: 14px; font-weight: 600; color: var(--slate-400);">unit</span></div>
        <div class="stat-sub" style="margin-top: 14px;">
          <i class="fa-solid fa-triangle-exclamation" style="color: var(--danger-500);"></i> Rusak / Butuh Repair: <strong>{{ $konRusak }} unit</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- Analytics Doughnut Charts Section -->
  <div class="card-panel">
    <div class="card-header-clean">
      <div>
        <h3 class="card-title-text"><i class="fa-solid fa-chart-pie" style="color: var(--primary-600);"></i> Distribusi Hasil Audit Opname</h3>
        <p class="card-subtitle-text">Analisis kelengkapan tagging label, kondisi fisik mesin/kendaraan, dan status utilisasi</p>
      </div>
      <div style="font-size: 12px; font-weight: 700; color: var(--primary-600); background: var(--primary-50); padding: 4px 10px; border-radius: 6px;">
        {{ $bulanTahunLabel }}
      </div>
    </div>

    <div class="charts-grid-3">
      <!-- Chart 1: Tagging Barcode -->
      <div class="chart-card">
        <div class="chart-header">
          <span><i class="fa-solid fa-tags" style="color: var(--accent-500); margin-right: 6px;"></i> Tagging Barcode Label</span>
        </div>
        <div class="chart-canvas-wrapper">
          <canvas id="chartTagging"></canvas>
        </div>
      </div>

      <!-- Chart 2: Physical Condition -->
      <div class="chart-card">
        <div class="chart-header">
          <span><i class="fa-solid fa-screwdriver-wrench" style="color: var(--success-500); margin-right: 6px;"></i> Kondisi Fisik Aset</span>
        </div>
        <div class="chart-canvas-wrapper">
          <canvas id="chartKondisi"></canvas>
        </div>
      </div>

      <!-- Chart 3: Usage Status -->
      <div class="chart-card">
        <div class="chart-header">
          <span><i class="fa-solid fa-arrows-spin" style="color: var(--primary-600); margin-right: 6px;"></i> Status Pemakaian Aset</span>
        </div>
        <div class="chart-canvas-wrapper">
          <canvas id="chartStatus"></canvas>
        </div>
      </div>
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
      cutout: '70%',
      borderWidth: 2,
      borderColor: '#ffffff',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: { size: 12, family: 'Plus Jakarta Sans', weight: '600' },
            padding: 14,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '700' },
          bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
          padding: 10,
          cornerRadius: 8
        }
      }
    };

    // 1. Chart Tagging
    new Chart(document.getElementById('chartTagging').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Ada Tagging Label', 'Belum Ada Label'],
        datasets: [{
          data: [tagAda, tagTidak],
          backgroundColor: ['#10b981', '#ef4444'],
          hoverOffset: 6
        }]
      },
      options: commonOptions
    });

    // 2. Chart Kondisi
    new Chart(document.getElementById('chartKondisi').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Kondisi Baik', 'Rusak / Afkir'],
        datasets: [{
          data: [konBaik, konRusak],
          backgroundColor: ['#10b981', '#f59e0b'],
          hoverOffset: 6
        }]
      },
      options: commonOptions
    });

    // 3. Chart Status Pemakaian
    new Chart(document.getElementById('chartStatus').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Sedang Digunakan', 'Idle Sementara', 'Idle Permanen'],
        datasets: [{
          data: [statGuna, statSem, statPerm],
          backgroundColor: ['#0f4c81', '#f59e0b', '#ef4444'],
          hoverOffset: 6
        }]
      },
      options: commonOptions
    });
  });
</script>
@endpush
