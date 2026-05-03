@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-title">Dashboard</div>
<div class="page-sub">Pharmacy inventory overview · Laravel Eloquent relationships</div>

{{-- ── Stat Cards ── --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-val" style="color:var(--accent)">{{ $stats['total_medicines'] }}</div>
        <div class="stat-label">Total Medicines</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--green)">{{ $stats['total_categories'] }}</div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--accent)">{{ $stats['total_suppliers'] }}</div>
        <div class="stat-label">Suppliers</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--amber)">{{ $stats['low_stock'] }}</div>
        <div class="stat-label">Low Stock (&lt;10)</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--red)">{{ $stats['out_of_stock'] }}</div>
        <div class="stat-label">Out of Stock</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--red)">{{ $stats['rx_required'] }}</div>
        <div class="stat-label">Rx Required</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    {{-- ── Low Stock Alerts (one-to-many: medicine→category) ── --}}
    <div class="card">
        <div class="card-title">⚠ Low Stock Alerts</div>
        @forelse($lowStockItems as $med)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-size:.85rem">{{ $med->medicine_name }}</div>
                    <div style="font-size:.72rem;color:var(--muted)">{{ $med->category?->category_name ?? 'Uncategorised' }}</div>
                </div>
                <span class="badge {{ $med->stock === 0 ? 'badge-out' : 'badge-low' }}">
                    {{ $med->stock === 0 ? 'OUT' : $med->stock . ' left' }}
                </span>
            </div>
        @empty
            <p style="color:var(--green);font-size:.82rem">✓ All medicines are well stocked.</p>
        @endforelse
    </div>

    {{-- ── Top Categories (one-to-many count) ── --}}
    <div class="card">
        <div class="card-title">📦 Top Categories by Medicines</div>
        @foreach($topCategories as $cat)
            <div style="margin-bottom:.85rem">
                <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
                    <a href="{{ route('categories.show', $cat) }}">{{ $cat->category_name }}</a>
                    <span style="color:var(--muted)">{{ $cat->medicines_count }}</span>
                </div>
                <div style="height:5px;background:var(--border);border-radius:3px">
                    <div style="height:100%;border-radius:3px;background:var(--accent);width:{{ $topCategories->first()->medicines_count > 0 ? round($cat->medicines_count / $topCategories->first()->medicines_count * 100) : 0 }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- ── Recent Medicines ── --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <div class="card-title" style="margin:0">🕐 Recently Added Medicines</div>
        <a href="{{ route('medicines.create') }}" class="btn btn-success btn-sm">+ Add Medicine</a>
    </div>
    <table class="data-table">
        <thead><tr>
            <th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Rx</th>
        </tr></thead>
        <tbody>
        @foreach($recentMedicines as $med)
            <tr>
                <td><a href="{{ route('medicines.show', $med) }}">{{ $med->medicine_name }}</a></td>
                <td><span class="badge badge-cat">{{ $med->category?->category_name ?? '—' }}</span></td>
                <td>${{ number_format($med->price, 2) }}</td>
                <td style="color:{{ $med->stock < 10 ? 'var(--red)' : 'var(--green)' }}">{{ $med->stock }}</td>
                <td><span class="badge {{ $med->prescription_required === 'YES' ? 'badge-yes' : 'badge-no' }}">{{ $med->prescription_required }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
