@extends('layouts.app')
@section('title', $category->category_name)

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
    <div>
        <div class="page-title">{{ $category->category_name }}</div>
        <div class="page-sub">
            <a href="{{ route('categories.index') }}">Categories</a> / View ·
            <span style="color:var(--muted)">{{ $category->description ?? 'No description' }}</span>
        </div>
    </div>
    <div>
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-ghost btn-sm">Edit</a>
    </div>
</div>

{{-- ── ONE-TO-MANY showcase ── --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <div>
            <div class="card-title" style="margin:0">Medicines in this Category</div>
            {{-- This is the ONE-TO-MANY relationship: $category->medicines --}}
            <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem">
                Eloquent: <code style="color:var(--accent)">$category->medicines()->paginate(10)</code>
                — hasMany(Medicine::class)
            </div>
        </div>
        <a href="{{ route('medicines.create') }}" class="btn btn-success btn-sm">+ Add Medicine</a>
    </div>

    <table class="data-table">
        <thead><tr>
            <th>Name</th><th>Price</th><th>Stock</th><th>Rx</th><th>Suppliers</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @forelse($medicines as $med)
            <tr>
                <td><a href="{{ route('medicines.show', $med) }}">{{ $med->medicine_name }}</a></td>
                <td>${{ number_format($med->price, 2) }}</td>
                <td style="color:{{ $med->stock < 10 ? 'var(--red)' : 'var(--green)' }}">{{ $med->stock }}</td>
                <td><span class="badge {{ $med->prescription_required === 'YES' ? 'badge-yes' : 'badge-no' }}">
                    {{ $med->prescription_required }}
                </span></td>
                <td style="color:var(--muted)">{{ $med->suppliers_count ?? '—' }}</td>
                <td>
                    <a href="{{ route('medicines.show', $med) }}" class="btn btn-ghost btn-sm">View</a>
                    <a href="{{ route('medicines.edit', $med) }}" class="btn btn-ghost btn-sm">Edit</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="color:var(--muted);padding:1rem">No medicines in this category yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- Pagination ── --}}
    @if($medicines->hasPages())
    <div class="pagination">
        @if($medicines->onFirstPage())
            <span class="page-item disabled"><span class="page-link">« Prev</span></span>
        @else
            <a class="page-item" href="{{ $medicines->previousPageUrl() }}"><span class="page-link">« Prev</span></a>
        @endif
        @foreach($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
            @if($page == $medicines->currentPage())
                <span class="page-item active"><span class="page-link">{{ $page }}</span></span>
            @else
                <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
            @endif
        @endforeach
        @if($medicines->hasMorePages())
            <a class="page-item" href="{{ $medicines->nextPageUrl() }}"><span class="page-link">Next »</span></a>
        @else
            <span class="page-item disabled"><span class="page-link">Next »</span></span>
        @endif
    </div>
    @endif
</div>
@endsection
