
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Brands') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="card mb-2">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Brands</h5>
                <a href="{{ route('admin.brands.create') }}" class="btn btn-sm product-action-btn btn-new mb-3">
                    <i class="cil-plus"></i> Add Brand
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x:unset; padding: 0 1rem;">
                    <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                        <thead>
                            <tr>
                                <th style="min-width: 80px;">ID</th>
                                <th style="min-width: 120px;">Name</th>
                                <th style="min-width: 200px;">Description</th>
                                <th style="min-width: 80px;">Logo</th>
                                <th style="min-width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                            <tr>
                                <td>{{ $brand->id }}</td>
                                <td>{{ $brand->name }}</td>
                                <td>{{ $brand->description }}</td>
                                <td>@if($brand->logo)<img src="{{ asset($brand->logo) }}" alt="Logo" style="height:40px;">@endif</td>
                                <td>
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn product-action-btn btn-outline-primary btn-sm me-1" title="Edit">
                                        <i class="cil-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn product-action-btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="cil-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No brands found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{-- Pagination if needed --}}
                {{-- {{ $brands->links('pagination::bootstrap-5') }} --}}
            </div>
        </div>
    </div>
    @push('styles')
    <style>
    .product-action-btn {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #6c757d !important;
    }
    .product-action-btn i {
        color: #6c757d !important;
    }
    .product-action-btn.btn-outline-primary:hover {
        background-color: #343a40 !important;
        border-color: #23272b !important;
    }
    .product-action-btn.btn-outline-primary:hover i {
        color: #fff !important;
    }
    .product-action-btn.btn-outline-danger:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }
    .product-action-btn.btn-outline-danger:hover i {
        color: #fff !important;
    }
    .product-action-btn.btn-new {
        border-color: #375a7f !important;
        color: #6c757d !important;
    }
    .product-action-btn.btn-new:hover {
        background-color: #375a7f !important;
        border-color: #2c3e50 !important;
        color: #fff !important;
    }
    </style>
    @endpush
</x-app-layout>
