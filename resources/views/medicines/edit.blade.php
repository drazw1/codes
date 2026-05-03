@extends('layouts.app')
@section('title', 'Edit Medicine')

@section('content')
<div class="page-title">Edit Medicine</div>
<div class="page-sub"><a href="{{ route('medicines.index') }}">Medicines</a> / <a href="{{ route('medicines.show', $medicine) }}">{{ $medicine->medicine_name }}</a> / Edit</div>

<form method="POST" action="{{ route('medicines.update', $medicine) }}">
@csrf @method('PUT')

{{-- Store currently attached supplier IDs for JS pre-checking --}}
@php $attachedIds = $medicine->suppliers->pluck('supplier_id')->toArray(); @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <div class="card">
        <div class="card-title">Medicine Details</div>

        <div class="form-group">
            <label>Medicine Name *</label>
            <input type="text" name="medicine_name" value="{{ old('medicine_name', $medicine->medicine_name) }}" required>
            @error('medicine_name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Category <span style="color:var(--accent);font-size:.7rem">(belongsTo)</span></label>
            <select name="category_id">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}"
                        {{ old('category_id', $medicine->category_id) == $cat->category_id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Price ($) *</label>
                <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $medicine->price) }}" required>
            </div>
            <div class="form-group">
                <label>Stock *</label>
                <input type="number" name="stock" min="0" value="{{ old('stock', $medicine->stock) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label>Prescription Required</label>
            <select name="prescription_required">
                <option value="NO"  {{ old('prescription_required', $medicine->prescription_required) === 'NO'  ? 'selected' : '' }}>No (OTC)</option>
                <option value="YES" {{ old('prescription_required', $medicine->prescription_required) === 'YES' ? 'selected' : '' }}>Yes (Rx)</option>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Suppliers <span style="color:var(--accent);font-size:.7rem">(belongsToMany → sync)</span></div>
        <div style="font-size:.75rem;color:var(--muted);margin-bottom:1rem">
            Saving calls <code style="color:var(--accent)">$medicine->suppliers()->sync($pivotData)</code>
            which replaces all pivot rows atomically.
        </div>

        @foreach($suppliers as $i => $sup)
        @php
            $isAttached = in_array($sup->supplier_id, $attachedIds);
            $pivot      = $isAttached ? $medicine->suppliers->find($sup->supplier_id)?->pivot : null;
        @endphp
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:7px;padding:.9rem;margin-bottom:.75rem">
            <label style="display:flex;gap:.6rem;align-items:center;cursor:pointer;margin-bottom:0">
                <input type="checkbox"
                       name="suppliers[{{ $i }}][id]"
                       value="{{ $sup->supplier_id }}"
                       class="sup-check" data-idx="{{ $i }}"
                       {{ $isAttached ? 'checked' : '' }}>
                <div>
                    <div style="font-weight:600;font-size:.85rem">{{ $sup->supplier_name }}</div>
                    <div style="font-size:.72rem;color:var(--muted)">{{ $sup->email ?? '' }}</div>
                </div>
            </label>

            <div class="pivot-fields" id="pivot-{{ $i }}"
                 style="display:{{ $isAttached ? 'grid' : 'none' }};margin-top:.75rem;grid-template-columns:1fr 1fr 1fr;gap:.5rem">
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Unit Cost ($)</label>
                    <input type="number" name="suppliers[{{ $i }}][unit_cost]" step="0.01" min="0"
                           value="{{ old("suppliers.$i.unit_cost", $pivot?->unit_cost ?? 0) }}"
                           style="font-size:.78rem;padding:.35rem .5rem">
                </div>
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Quantity</label>
                    <input type="number" name="suppliers[{{ $i }}][quantity]" min="0"
                           value="{{ old("suppliers.$i.quantity", $pivot?->quantity ?? 0) }}"
                           style="font-size:.78rem;padding:.35rem .5rem">
                </div>
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Last Supplied</label>
                    <input type="date" name="suppliers[{{ $i }}][last_supplied_at]"
                           value="{{ old("suppliers.$i.last_supplied_at", $pivot?->last_supplied_at ?? '') }}"
                           style="font-size:.78rem;padding:.35rem .5rem">
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

<div style="display:flex;gap:.75rem;margin-top:.5rem">
    <button type="submit" class="btn btn-primary">Update Medicine</button>
    <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-ghost">Cancel</a>
</div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.sup-check').forEach(function(chk) {
    var fields = document.getElementById('pivot-' + chk.dataset.idx);
    chk.addEventListener('change', function() {
        fields.style.display = this.checked ? 'grid' : 'none';
    });
});
</script>
@endpush
