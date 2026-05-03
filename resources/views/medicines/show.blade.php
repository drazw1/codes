@extends('layouts.app')
@section('title', $medicine->medicine_name)

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
    <div>
        <div class="page-title">{{ $medicine->medicine_name }}</div>
        <div class="page-sub"><a href="{{ route('medicines.index') }}">Medicines</a> / View</div>
    </div>
    <div style="display:flex;gap:.5rem">
        <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-primary btn-sm">Edit</a>
        <form method="POST" action="{{ route('medicines.destroy', $medicine) }}"
              onsubmit="return confirm('Delete this medicine?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete</button>
        </form>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    {{-- ── Medicine details + ONE-TO-MANY ── --}}
    <div class="card">
        <div class="card-title">Medicine Details</div>

        <table style="width:100%;font-size:.85rem;border-collapse:collapse">
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:.5rem;color:var(--muted);width:40%">ID</td>
                <td style="padding:.5rem">#{{ $medicine->medicine_id }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:.5rem;color:var(--muted)">Name</td>
                <td style="padding:.5rem"><strong>{{ $medicine->medicine_name }}</strong></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                {{-- ONE-TO-MANY (belongsTo) --}}
                <td style="padding:.5rem;color:var(--muted)">
                    Category
                    <div style="font-size:.68rem;color:var(--accent)">belongsTo</div>
                </td>
                <td style="padding:.5rem">
                    @if($medicine->category)
                        <a href="{{ route('categories.show', $medicine->category) }}" class="badge badge-cat">
                            {{ $medicine->category->category_name }}
                        </a>
                    @else
                        <span style="color:var(--muted)">Uncategorised</span>
                    @endif
                </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:.5rem;color:var(--muted)">Price</td>
                <td style="padding:.5rem;color:var(--green);font-weight:700">${{ number_format($medicine->price, 2) }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:.5rem;color:var(--muted)">Stock</td>
                <td style="padding:.5rem">
                    <span style="font-weight:700;color:{{ $medicine->stock === 0 ? 'var(--red)' : ($medicine->stock < 10 ? 'var(--amber)' : 'var(--green)') }}">
                        {{ $medicine->stock }} units
                    </span>
                    @if($medicine->stock === 0) <span class="badge badge-out">OUT OF STOCK</span>
                    @elseif($medicine->stock < 10) <span class="badge badge-low">LOW</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding:.5rem;color:var(--muted)">Prescription</td>
                <td style="padding:.5rem">
                    <span class="badge {{ $medicine->prescription_required === 'YES' ? 'badge-yes' : 'badge-no' }}">
                        {{ $medicine->prescription_required === 'YES' ? 'Rx Required' : 'Over the Counter' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── MANY-TO-MANY: Suppliers ── --}}
    <div class="card">
        <div class="card-title">Suppliers</div>
        <div style="font-size:.72rem;color:var(--accent);margin-bottom:.75rem">
            Eloquent: <code>$medicine->suppliers</code> — belongsToMany via medicine_supplier pivot
        </div>

        @forelse($medicine->suppliers as $sup)
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:.9rem;margin-bottom:.75rem">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <div style="font-weight:700;font-size:.9rem">{{ $sup->supplier_name }}</div>
                        <div style="color:var(--muted);font-size:.75rem">{{ $sup->contact_person ?? '' }}</div>
                    </div>
                    <a href="{{ route('suppliers.show', $sup) }}" class="btn btn-ghost btn-sm">View</a>
                </div>
                {{-- Pivot data ── --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-top:.75rem;font-size:.78rem">
                    <div style="background:var(--card);padding:.5rem;border-radius:5px">
                        <div style="color:var(--muted);font-size:.68rem">UNIT COST</div>
                        <div style="color:var(--amber);font-weight:700">${{ number_format($sup->pivot->unit_cost, 2) }}</div>
                    </div>
                    <div style="background:var(--card);padding:.5rem;border-radius:5px">
                        <div style="color:var(--muted);font-size:.68rem">QTY AVAILABLE</div>
                        <div style="color:var(--accent);font-weight:700">{{ $sup->pivot->quantity }}</div>
                    </div>
                    <div style="background:var(--card);padding:.5rem;border-radius:5px">
                        <div style="color:var(--muted);font-size:.68rem">LAST SUPPLIED</div>
                        <div style="font-weight:700">{{ $sup->pivot->last_supplied_at ?? '—' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <p style="color:var(--muted);font-size:.82rem">No suppliers linked to this medicine.</p>
        @endforelse
    </div>

</div>
@endsection
