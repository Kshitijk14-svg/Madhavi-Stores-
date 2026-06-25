@extends('admin.layout')
@section('admin_title', 'Overview')

@section('admin_content')
<div class="mb-5">
  <h2 class="font-display text-2xl text-primary">Overview & Statistics</h2>
  <p class="text-[11px] text-muted mt-0.5">A summary of collections, catalog, and active users.</p>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 gap-3 mb-8">
  @foreach($stats as $stat)
  <div class="border border-gray-100 bg-white p-4 flex flex-col justify-between">
    <span class="text-[9px] font-bold tracking-[0.15em] uppercase text-muted">{{ $stat['label'] }}</span>
    <div class="flex items-end justify-between mt-2">
      <span class="text-2xl font-display font-medium text-primary leading-none">{{ $stat['value'] }}</span>
      <span class="text-[8px] font-bold uppercase text-secondary bg-secondary/10 px-1.5 py-0.5">{{ $stat['trend'] }}</span>
    </div>
  </div>
  @endforeach
</div>

{{-- Sales chart --}}
<div class="border border-gray-100 bg-white p-4 mb-8">
  <div class="mb-4">
    <span class="text-[9px] font-bold tracking-[0.15em] uppercase text-muted">Business Performance</span>
    <h3 class="font-display text-xl text-primary mt-0.5">Sales Revenue</h3>
  </div>

  {{-- Interval dropdown (replaces the desktop button row) --}}
  <div class="space-y-2 mb-4">
    <select id="chart-range-select" onchange="loadSalesChart(this.value)"
            class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="day">Today</option>
      <option value="week">This Week</option>
      <option value="month" selected>This Month</option>
      <option value="year">This Year</option>
      <option value="all">All Time</option>
    </select>
    <div class="flex items-center gap-2">
      <input type="date" id="chart-start-date" class="flex-1 text-[11px] text-muted bg-white border border-gray-200 px-2 py-2 outline-none">
      <span class="text-[10px] text-muted">to</span>
      <input type="date" id="chart-end-date" onchange="loadSalesChart('custom')" class="flex-1 text-[11px] text-muted bg-white border border-gray-200 px-2 py-2 outline-none">
    </div>
  </div>

  <div class="border-y border-gray-100 py-3 mb-4">
    <p class="text-[11px] text-muted">Period Sales Total</p>
    <p id="chart-total-sales" class="text-2xl font-display font-medium text-secondary mt-0.5">₹0.00</p>
  </div>

  <div class="relative w-full" style="height:240px;">
    <canvas id="salesChart"></canvas>
  </div>
</div>

{{-- Recent products --}}
<div class="flex items-center justify-between mb-4">
  <h3 class="font-display text-lg text-primary">Recently Added</h3>
  <a href="{{ route('admin.products.index') }}" class="text-[10px] font-bold tracking-widest uppercase text-secondary">View All →</a>
</div>

<div class="space-y-3">
  @forelse($recentProducts as $product)
    <div class="flex items-center gap-3 border border-gray-100 bg-white p-3">
      @if($product->image_url)
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-12 h-14 object-cover bg-silk shrink-0">
      @else
        <div class="w-12 h-14 bg-silk flex items-center justify-center text-[9px] text-muted shrink-0">No Image</div>
      @endif
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-primary truncate">{{ $product->name }}</p>
        <p class="text-[10px] text-muted">{{ $product->category->name ?? 'No collection' }} · ₹{{ number_format($product->price, 2) }}</p>
        <div class="mt-1">
          @if($product->is_bestseller)
            <span class="text-[8px] font-bold uppercase text-emerald-700 bg-emerald-50 px-1.5 py-0.5 border border-emerald-200">Bestseller</span>
          @else
            <span class="text-[8px] font-bold uppercase text-gray-500 bg-gray-50 px-1.5 py-0.5 border border-gray-200">Standard</span>
          @endif
        </div>
      </div>
      <a href="{{ route('admin.products.edit', $product->id) }}"
         class="text-[10px] font-bold tracking-wider uppercase text-white bg-primary px-3 py-2 shrink-0">Edit</a>
    </div>
  @empty
    <div class="text-center py-8 text-muted text-sm border border-dashed border-gray-200">No products yet.</div>
  @endforelse
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
  let salesChartInstance = null;
  function loadSalesChart(range = 'month') {
    if (typeof Chart === 'undefined') {
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js';
      s.onload = () => loadSalesChart(range);
      document.head.appendChild(s);
      return;
    }

    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    let fetchUrl = `{{ url('/admin/sales-chart-data') }}?range=${range}`;
    if (range === 'custom') {
      const start = document.getElementById('chart-start-date').value;
      const end = document.getElementById('chart-end-date').value;
      if (start && end) {
        fetchUrl += `&start=${start}&end=${end}`;
      } else {
        showToast('Please select both a start and end date.', 'error');
        return;
      }
    }

    fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const totalSpan = document.getElementById('chart-total-sales');
        if (totalSpan) totalSpan.innerText = data.total;

        const ctx = canvas.getContext('2d');
        if (salesChartInstance) salesChartInstance.destroy();

        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(235, 184, 41, 0.25)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0.0)');

        salesChartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Sales Revenue',
              data: data.values,
              borderColor: '#ebb829',
              borderWidth: 2,
              backgroundColor: gradient,
              fill: true,
              tension: 0.35,
              pointBackgroundColor: '#ebb829',
              pointBorderColor: '#fff',
              pointRadius: 2,
              pointHoverRadius: 5
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: '#181818', titleColor: '#ebb829', bodyColor: '#fff',
                cornerRadius: 0, padding: 10, displayColors: false,
                callbacks: { label: c => '₹' + Number(c.parsed.y).toLocaleString('en-IN') }
              }
            },
            scales: {
              x: { grid: { display: false }, ticks: { color: '#888', font: { size: 9 } } },
              y: {
                min: 0, suggestedMax: 1000,
                grid: { color: '#f0ebe3' },
                ticks: { color: '#888', font: { size: 9 }, callback: v => '₹' + Number(v).toLocaleString('en-IN') }
              }
            }
          }
        });
      })
      .catch(err => console.error('Sales chart error:', err));
  }

  document.addEventListener('DOMContentLoaded', () => loadSalesChart('month'));
</script>
@endsection
