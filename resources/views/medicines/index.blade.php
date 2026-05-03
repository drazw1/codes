@extends('layouts.app')
@section('title', 'Medicines')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem">
    <div>
        <div class="page-title">Medicines</div>
        <div class="page-sub">Full CRUD · One-to-Many (category) · Many-to-Many (suppliers) · {{ $medicines->total() }} total</div>
    </div>
    <a href="{{ route('medicines.create') }}" class="btn btn-success">+ New Medicine</a>
</div>

{{-- ── Search / Filter ── --}}
<div class="card" style="padding:1rem 1.5rem;margin-bottom:1rem">
    <form method="GET" action="{{ route('medicines.index') }}" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:2;min-width:160px">
            <label>Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Medicine name…">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label>Category</label>
            <select name="category_id">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}" {{ request('category_id') == $cat->category_id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:120px">
            <label>Prescription</label>
            <select name="rx">
                <option value="">All</option>
                <option value="YES" {{ request('rx') === 'YES' ? 'selected' : '' }}>Rx Only</option>
                <option value="NO"  {{ request('rx') === 'NO'  ? 'selected' : '' }}>OTC</option>
            </select>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('medicines.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>
</div>

{{-- ── Table ── --}}
<div class="card">
    <table class="data-table">
        <thead><tr>
            <th>#</th>
            <th>Medicine Name</th>
            <th>Category <span style="color:var(--muted);font-weight:400;font-size:.7rem">(1:M)</span></th>
            <th>Suppliers <span style="color:var(--muted);font-weight:400;font-size:.7rem">(M:M)</span></th>
            <th style="text-align:right">Price</th>
            <th style="text-align:center">Stock</th>
            <th>Rx</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        @forelse($medicines as $med)
            <tr>
                <td style="color:var(--muted)">{{ $med->medicine_id }}</td>
                <td><a href="{{ route('medicines.show', $med) }}"><strong>{{ $med->medicine_name }}</strong></a></td>
                {{-- one-to-many: $med->category loaded via eager loading --}}
                <td>
                    @if($med->category)
                        <a href="{{ route('categories.show', $med->category) }}" class="badge badge-cat">
                            {{ $med->category->category_name }}
                        </a>
                    @else
                        <span style="color:var(--muted)">—</span>
                    @endif
                </td>
                {{-- many-to-many: $med->suppliers collection --}}
                <td>
                    @if($med->suppliers->count())
                        @foreach($med->suppliers->take(2) as $sup)
                            <span class="badge" style="background:rgba(74,222,128,.1);color:var(--green);margin-right:2px">
                                {{ $sup->supplier_name }}
                            </span>
                        @endforeach
                        @if($med->suppliers->count() > 2)
                            <span style="color:var(--muted);font-size:.72rem">+{{ $med->suppliers->count() - 2 }} more</span>
                        @endif
                    @else
                        <span style="color:var(--muted)">None</span>
                    @endif
                </td>
                <td style="text-align:right">${{ number_format($med->price, 2) }}</td>
                <td style="text-align:center">
                    @if($med->stock === 0)
                        <span class="badge badge-out">OUT</span>
                    @elseif($med->stock < 10)
                        <span class="badge badge-low">{{ $med->stock }}</span>
                    @else
                        <span style="color:var(--green)">{{ $med->stock }}</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $med->prescription_required === 'YES' ? 'badge-yes' : 'badge-no' }}">
                        {{ $med->prescription_required }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('medicines.show', $med) }}"   class="btn btn-ghost btn-sm">View</a>
                    <a href="{{ route('medicines.edit', $med) }}"   class="btn btn-ghost btn-sm">Edit</a>
                    <form method="POST" action="{{ route('medicines.destroy', $med) }}" style="display:inline"
                          onsubmit="return confirm('Delete {{ addslashes($med->medicine_name) }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Del</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" style="color:var(--muted);padding:1.5rem">No medicines found.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- ── Pagination (withQueryString keeps filter params) ── --}}
    @if($medicines->hasPages())
    <div class="pagination">
        @if($medicines->onFirstPage())
            <span class="page-item disabled"><span class="page-link">« Prev</span></span>
        @else
            <a class="page-item" href="{{ $medicines->previousPageUrl() }}"><span class="page-link">« Prev</span></a>
        @endif
        @foreach($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
            <a class="page-item {{ $page == $medicines->currentPage() ? 'active' : '' }}" href="{{ $url }}">
                <span class="page-link">{{ $page }}</span>
            </a>
        @endforeach
        @if($medicines->hasMorePages())
            <a class="page-item" href="{{ $medicines->nextPageUrl() }}"><span class="page-link">Next »</span></a>
        @else
            <span class="page-item disabled"><span class="page-link">Next »</span></span>
        @endif
    </div>
    <p style="font-size:.75rem;color:var(--muted);margin-top:.5rem">
        Showing {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }} of {{ $medicines->total() }} medicines
    </p>
    @endif
</div>
@endsection
