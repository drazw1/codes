@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
    <div>
        <div class="page-title">Categories</div>
        <div class="page-sub">One-to-Many: each category owns many medicines · {{ $categories->total() }} total</div>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-success">+ New Category</a>
</div>

<div class="card">
    <table class="data-table">
        <thead><tr>
            <th>#</th>
            <th>Category Name</th>
            <th>Description</th>
            <th style="text-align:center">Medicines</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        @forelse($categories as $cat)
            <tr>
                <td style="color:var(--muted)">{{ $cat->category_id }}</td>
                <td><a href="{{ route('categories.show', $cat) }}"><strong>{{ $cat->category_name }}</strong></a></td>
                <td style="color:var(--muted)">{{ $cat->description ?? '—' }}</td>
                <td style="text-align:center">
                    <span class="badge badge-cat">{{ $cat->medicines_count }}</span>
                </td>
                <td>
                    <a href="{{ route('categories.show', $cat) }}"   class="btn btn-ghost btn-sm">View</a>
                    <a href="{{ route('categories.edit', $cat) }}"   class="btn btn-ghost btn-sm">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" style="display:inline"
                          onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="color:var(--muted);padding:1.5rem">No categories yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- ── Pagination ── --}}
    {{-- $categories->links() renders the paginator using Tailwind by default.
         We override with our custom links() call below. --}}
    <div class="pagination">
        {{-- Previous --}}
        @if($categories->onFirstPage())
            <span class="page-item disabled"><span class="page-link">« Prev</span></span>
        @else
            <a class="page-item" href="{{ $categories->previousPageUrl() }}"><span class="page-link">« Prev</span></a>
        @endif

        {{-- Page numbers --}}
        @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
            @if($page == $categories->currentPage())
                <span class="page-item active"><span class="page-link">{{ $page }}</span></span>
            @else
                <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($categories->hasMorePages())
            <a class="page-item" href="{{ $categories->nextPageUrl() }}"><span class="page-link">Next »</span></a>
        @else
            <span class="page-item disabled"><span class="page-link">Next »</span></span>
        @endif
    </div>
    <p style="font-size:.75rem;color:var(--muted);margin-top:.5rem">
        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
    </p>
</div>
@endsection
