
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Master Product') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card mb-4">
            <div class="card-header">
                Edit Master Product
            </div>
            <div class="card-body">
                <form action="{{ route('admin.master-products.update', $masterProduct) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $masterProduct->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="synonyms" class="form-label">Synonyms</label>
                        <textarea class="form-control @error('synonyms') is-invalid @enderror" id="synonyms" name="synonyms" rows="3" placeholder="Comma-separated synonyms">{{ old('synonyms', $masterProduct->synonyms) }}</textarea>
                        <div class="form-text">Enter synonyms separated by commas.</div>
                        @error('synonyms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="cil-info"></i> Updating this product will mark it as unsynced until the next sync operation.
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.master-products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Master Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
