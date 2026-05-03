@extends('layouts.app')
@section('title', 'New Category')

@section('content')
<div class="page-title">New Category</div>
<div class="page-sub"><a href="{{ route('categories.index') }}">Categories</a> / Create</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf

        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="category_name" value="{{ old('category_name') }}" required>
            @error('category_name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Description</label>
            <input type="text" name="description" value="{{ old('description') }}">
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1rem">
            <button type="submit" class="btn btn-success">Save Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
