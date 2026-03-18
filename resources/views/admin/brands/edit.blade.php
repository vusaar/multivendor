
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Brand') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="glass-card mb-4">
                    <div class="card-header bg-transparent border-0 p-4">
                        <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit Brand</h4>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('admin.brands.update', $brand) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Name</label>
                                    <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name', $brand->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="logo" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Logo URL</label>
                                    <input type="text" name="logo" id="logo" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('logo', $brand->logo) }}">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Description</label>
                                <textarea name="description" id="description" class="form-control border-0 bg-light rounded-3" rows="4">{{ old('description', $brand->description) }}</textarea>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary px-4">Update Brand</button>
                                <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
