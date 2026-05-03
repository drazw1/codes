@extends('layouts.app')
@section('title', $supplier->supplier_name)
@section('content')

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
    <div>
        <div class="page-title">{{ $supplier->supplier_name }}</div>
        <div class="page-sub"><a href="{{ route('suppliers.index') }}">Suppliers</a> / View</div>
    </div>
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary btn-sm">Edit</a>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem">

    <div class="card">
        <div class="card-title">Contact Details</div>
        <div style="font-size:.85rem;line-height:2">
            <div style="color:var(--muted);font-size:.72rem">CONTACT</div>
            <div>{{ $supplier->contact_person ?? '—' }}</div>
            <div style="color:var(--muted);font-size:.72rem;margin-top:.5rem">EMAIL</div>
            <div>{{ $supplier->email ?? '—' }}</div>
            <div style="color:var(--muted);font-size:.72rem;margin-top:.5rem">PHONE</div>
            <div>{{ $supplier->phone ?? '—' }}</div>
            <div style="color:var(--muted);font-size:.72rem;margin-top:.5rem">ADDRESS</div>
            <div>{{ $supplier->address ?? '—' }}</div>
        </div>
    </div>

    {{-- MANY-TO-MANY from the supplier side --}}
    <div class="card">
        <div class="card-title">Medicines Supplied</div>
        <div style="font-size:.72rem;color:var(--accent);margin-bottom:.75rem">
            Eloquent: <code>$supplier->medicines()</code> — belongsToMany with pivot data
        </div>

        <table class="data-table">
            <thead><tr>
                <th>Medicine</th><th>Category</th>
                <th>Unit Cost</th><th>Qty Available</th><th>Last Supplied</th><th>Retail Price</th>
            </tr></thead>
            <tbody>
            @forelse($medicines as $med)
                <tr>
                    <td><a href="{{ route('medicines.show', $med) }}">{{ $med->medicine_name }}</a></td>
                    <td><span class="badge badge-cat">{{ $med->category?->category_name ?? '—' }}</span></td>
                    {{-- Pivot columns accessed via $med->pivot --}}
                    <td style="color:var(--amber)">${{ number_format($med->pivot->unit_cost, 2) }}</td>
                    <td style="color:var(--accent)">{{ $med->pivot->quantity }}</td>
                    <td style="color:var(--muted)">{{ $med->pivot->last_supplied_at ?? '—' }}</td>
                    <td>${{ number_format($med->price, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="color:var(--muted);padding:1rem">No medicines linked yet.</td></tr>
            @endforelse
            </tbody>
        </table>

        @if($medicines->hasPages())
        <div class="pagination">
            @if($medicines->onFirstPage())
                <span class="page-item disabled"><span class="page-link">« Prev</span></span>
            @else
                <a class="page-item" href="{{ $medicines->previousPageUrl() }}"><span class="page-link">« Prev</span></a>
            @endif
            @foreach($medicines->getUrlRange(1,$medicines->lastPage()) as $page => $url)
                <a class="page-item {{ $page==$medicines->currentPage()?'active':'' }}" href="{{ $url }}">
                    <span class="page-link">{{ $page }}</span>
                </a>
            @endforeach
            @if($medicines->hasMorePages())
                <a class="page-item" href="{{ $medicines->nextPageUrl() }}"><span class="page-link">Next »</span></a>
            @else
                <span class="page-item disabled"><span class="page-link">Next »</span></span>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
