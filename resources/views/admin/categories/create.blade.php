<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Category') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Create Category</h5>
        </div>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="parent_id" class="form-label">Parent Category</label>
                    <select name="parent_id" id="parent_id" class="form-select select2">
                        <option value="">None</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-outline active"><i class="cil-check"></i> Create</button>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.select2) {
            $('#parent_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'None',
                allowClear: true
            });
        } else {
            console.error('Select2 or jQuery not loaded');
        }
    });
</script>
@endpush
</x-app-layout>
