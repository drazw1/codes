@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<div class="page-title">Edit Supplier</div>
<div class="page-sub"><a href="{{ route('suppliers.index') }}">Suppliers</a> / Edit</div>

<div class="card" style="max-width:600px">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label>Supplier Name *</label>
            <input type="text" name="supplier_name" value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
            @error('supplier_name') <div class="form-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label>Contact Person</label>
            <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="{{ old('address', $supplier->address) }}">
        </div>
        <div style="display:flex;gap:.75rem;margin-top:.5rem">
            <button type="submit" class="btn btn-primary">Update Supplier</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
