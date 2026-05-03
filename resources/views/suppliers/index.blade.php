{{-- resources/views/suppliers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
    <div>
        <div class="page-title">Suppliers</div>
        <div class="page-sub">Many-to-Many: each supplier can supply many medicines · {{ $suppliers->total() }} total</div>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-success">+ New Supplier</a>
</div>

<div class="card">
    <table class="data-table">
        <thead><tr>
            <th>#</th><th>Supplier Name</th><th>Contact</th><th>Email</th><th>Phone</th>
            <th style="text-align:center">Medicines</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @forelse($suppliers as $sup)
            <tr>
                <td style="color:var(--muted)">{{ $sup->supplier_id }}</td>
                <td><a href="{{ route('suppliers.show', $sup) }}"><strong>{{ $sup->supplier_name }}</strong></a></td>
                <td style="color:var(--muted)">{{ $sup->contact_person ?? '—' }}</td>
                <td style="color:var(--muted)">{{ $sup->email ?? '—' }}</td>
                <td style="color:var(--muted)">{{ $sup->phone ?? '—' }}</td>
                <td style="text-align:center"><span class="badge badge-cat">{{ $sup->medicines_count }}</span></td>
                <td>
                    <a href="{{ route('suppliers.show', $sup) }}"   class="btn btn-ghost btn-sm">View</a>
                    <a href="{{ route('suppliers.edit', $sup) }}"   class="btn btn-ghost btn-sm">Edit</a>
                    <form method="POST" action="{{ route('suppliers.destroy', $sup) }}" style="display:inline"
                          onsubmit="return confirm('Delete {{ addslashes($sup->supplier_name) }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Del</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="color:var(--muted);padding:1.5rem">No suppliers yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    @if($suppliers->hasPages())
    <div class="pagination">
        @if($suppliers->onFirstPage())
            <span class="page-item disabled"><span class="page-link">« Prev</span></span>
        @else
            <a class="page-item" href="{{ $suppliers->previousPageUrl() }}"><span class="page-link">« Prev</span></a>
        @endif
        @foreach($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
            <a class="page-item {{ $page == $suppliers->currentPage() ? 'active' : '' }}" href="{{ $url }}">
                <span class="page-link">{{ $page }}</span>
            </a>
        @endforeach
        @if($suppliers->hasMorePages())
            <a class="page-item" href="{{ $suppliers->nextPageUrl() }}"><span class="page-link">Next »</span></a>
        @else
            <span class="page-item disabled"><span class="page-link">Next »</span></span>
        @endif
    </div>
    @endif
</div>
@endsection
