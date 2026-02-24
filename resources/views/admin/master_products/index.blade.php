
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Master Products') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        
        {{-- Success/Error Messages (if not handled globally) --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-2">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Products</h5>
                <div>
                    {{-- Sync Button --}}
                    <form action="{{ route('admin.master-products.sync') }}" method="POST" class="d-inline-block me-2">
                        @csrf
                        <button type="submit" class="btn btn-sm product-action-btn mb-3" title="Sync Synonyms to Meilisearch">
                            <i class="cil-sync"></i> Sync to Meilisearch
                        </button>
                    </form>

                    <a href="{{ route('admin.master-products.create') }}" class="btn btn-sm product-action-btn btn-new mb-3">
                        <i class="cil-plus"></i> Add Master Product
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x:unset; padding: 0 1rem;">
                    <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                        <thead>
                            <tr>
                                <th style="min-width: 60px; width: 60px;">ID</th>
                                <th style="min-width: 150px;">Name</th>
                                <th style="min-width: 250px;">Synonyms</th>
                                <th style="min-width: 100px;">Synced?</th>
                                <th style="min-width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($masterProducts as $mp)
                            <tr>
                                <td>{{ $mp->id }}</td>
                                <td>{{ $mp->name }}</td>
                                <td>{{ $mp->synonyms }}</td>
                                <td>
                                    @if($mp->is_synced)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-warning text-dark">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.master-products.edit', $mp) }}" class="btn product-action-btn btn-outline-primary btn-sm me-1" title="Edit">
                                        <i class="cil-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.master-products.destroy', $mp) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this master product?');">
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
                                <td colspan="5" class="text-center">No master products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $masterProducts->links('pagination::bootstrap-5') }}
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
    .product-action-btn:hover {
        background-color: #e2e6ea !important;
        color: #495057 !important;
    }
    .product-action-btn.btn-outline-primary:hover {
        background-color: #343a40 !important;
        border-color: #23272b !important;
        color: #fff !important;
    }
    .product-action-btn.btn-outline-primary:hover i {
        color: #fff !important;
    }
    .product-action-btn.btn-outline-danger:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
        color: #fff !important;
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
