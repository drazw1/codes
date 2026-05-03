@extends('layouts.app')
@section('title', 'New Medicine')

@section('content')
<div class="page-title">New Medicine</div>
<div class="page-sub"><a href="{{ route('medicines.index') }}">Medicines</a> / Create</div>

<form method="POST" action="{{ route('medicines.store') }}">
@csrf

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    {{-- ── Core fields ── --}}
    <div class="card">
        <div class="card-title">Medicine Details</div>

        <div class="form-group">
            <label>Medicine Name *</label>
            <input type="text" name="medicine_name" value="{{ old('medicine_name') }}" required>
            @error('medicine_name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            {{-- ONE-TO-MANY relationship picker --}}
            <label>Category <span style="color:var(--accent);font-size:.7rem">(One-to-Many → belongsTo)</span></label>
            <select name="category_id">
                <option value="">— Select Category —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}" {{ old('category_id') == $cat->category_id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Price ($) *</label>
                <input type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" required>
                @error('price') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="stock" min="0" value="{{ old('stock', 0) }}" required>
                @error('stock') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Prescription Required *</label>
            <select name="prescription_required">
                <option value="NO"  {{ old('prescription_required', 'NO') === 'NO'  ? 'selected' : '' }}>No (OTC)</option>
                <option value="YES" {{ old('prescription_required') === 'YES' ? 'selected' : '' }}>Yes (Rx)</option>
            </select>
        </div>
    </div>

    {{-- ── Many-to-Many: Suppliers ── --}}
    <div class="card">
        <div class="card-title">Link Suppliers <span style="color:var(--accent);font-size:.7rem">(Many-to-Many)</span></div>
        <div style="font-size:.75rem;color:var(--muted);margin-bottom:1rem;line-height:1.5">
            Check suppliers and enter pivot data (unit cost, quantity, last supplied).
            Stored in the <code>medicine_supplier</code> pivot table.
        </div>

        @foreach($suppliers as $i => $sup)
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:7px;padding:.9rem;margin-bottom:.75rem">
            <label style="display:flex;gap:.6rem;align-items:center;cursor:pointer;margin-bottom:0">
                <input type="checkbox"
                       name="suppliers[{{ $i }}][id]"
                       value="{{ $sup->supplier_id }}"
                       class="sup-check"
                       data-idx="{{ $i }}"
                       {{ collect(old('suppliers', []))->pluck('id')->contains($sup->supplier_id) ? 'checked' : '' }}>
                <div>
                    <div style="font-weight:600;font-size:.85rem">{{ $sup->supplier_name }}</div>
                    <div style="font-size:.72rem;color:var(--muted)">{{ $sup->email ?? $sup->phone ?? '' }}</div>
                </div>
            </label>

            {{-- Pivot fields – hidden until checkbox checked --}}
            <div class="pivot-fields" id="pivot-{{ $i }}" style="display:none;margin-top:.75rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Unit Cost ($)</label>
                    <input type="number" name="suppliers[{{ $i }}][unit_cost]" step="0.01" min="0"
                           value="{{ old("suppliers.$i.unit_cost", 0) }}" style="font-size:.78rem;padding:.35rem .5rem">
                </div>
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Quantity</label>
                    <input type="number" name="suppliers[{{ $i }}][quantity]" min="0"
                           value="{{ old("suppliers.$i.quantity", 0) }}" style="font-size:.78rem;padding:.35rem .5rem">
                </div>
                <div>
                    <label style="font-size:.7rem;color:var(--muted)">Last Supplied</label>
                    <input type="date" name="suppliers[{{ $i }}][last_supplied_at]"
                           value="{{ old("suppliers.$i.last_supplied_at") }}" style="font-size:.78rem;padding:.35rem .5rem">
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

<div style="display:flex;gap:.75rem;margin-top:.5rem">
    <button type="submit" class="btn btn-success">Save Medicine</button>
    <a href="{{ route('medicines.index') }}" class="btn btn-ghost">Cancel</a>
</div>

</form>
@endsection

@push('scripts')
<script>
// Show/hide pivot fields when supplier checkbox is toggled
document.querySelectorAll('.sup-check').forEach(function(chk) {
    var idx    = chk.dataset.idx;
    var fields = document.getElementById('pivot-' + idx);

    // Set initial state
    fields.style.display = chk.checked ? 'grid' : 'none';

    chk.addEventListener('change', function() {
        fields.style.display = this.checked ? 'grid' : 'none';
    });
});
</script>
@endpush
