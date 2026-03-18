
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Brands') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Brands</h4>
                <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                    <i class="cil-plus"></i> Add Brand
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 80px;">ID</th>
                                <th style="width: 150px;">Logo</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-end pe-4" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                            <tr>
                                <td class="ps-4"><span class="text-muted small">#{{ $brand->id }}</span></td>
                                <td>
                                    @if($brand->logo)
                                        <div class="brand-logo-container">
                                            <img src="{{ asset($brand->logo) }}" alt="Logo">
                                        </div>
                                    @else
                                        <div class="brand-logo-placeholder">
                                            {{ substr($brand->name, 0, 1) }}
                                        </div>
                                    @endif
                                </td>
                                <td><span class="fw-bold">{{ $brand->name }}</span></td>
                                <td><span class="text-muted small">{{ Str::limit($brand->description, 100) }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                                <i class="cil-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No brands found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .brand-logo-container {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .brand-logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .brand-logo-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--pm-bg-subtle);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }
    </style>
    @endpush
</x-app-layout>
