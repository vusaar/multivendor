<x-app-layout>
    <x-slot name="header">
        
    </x-slot>
    <div class="container-fluid py-4">
        <div class="card mb-2">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Products</h5>
                <a href="{{ route('admin.products.create') }}" class="btn btn-sm product-action-btn btn-new mb-3">
                    <i class="cil-plus"></i> Add Product
                </a>
            </div>
            <div class="card-body p-0">
                <form method="GET" class="row g-2 m-3 p-2 align-items-end">
                    <div class="col-md-11">
                        <div class="d-flex flex-wrap align-items-end overflow-auto"
                            style="white-space:nowrap; min-height:60px; max-width:100vw;">
                            <div class="me-1 mb-2" style="min-width:180px;">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                    placeholder="Search by name...">
                            </div>
                            <div class="me-2 mb-2" style="min-width:150px;">
                                <select name="vendor_id" class="form-select">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->shop_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="me-2 mb-2" style="min-width:150px;">
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="me-2 mb-2" style="min-width:130px;">
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="me-2 mb-2" style="min-width:110px;">
                                <input type="number" name="price_min" value="{{ request('price_min') }}" class="form-control"
                                    placeholder="Min Price" min="0" step="0.01">
                            </div>
                            <div class="me-2 mb-2" style="min-width:110px;">
                                <input type="number" name="price_max" value="{{ request('price_max') }}" class="form-control"
                                    placeholder="Max Price" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex flex-column align-items-end gap-2 mb-2">
                        <button class="btn product-action-btn btn-outline-primary w-100 mb-2" type="submit"><i class="cil-search"></i>
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn product-action-btn btn-outline-secondary w-100">Reset
                        </a>
                    </div>
                </form>
                <div class="table-responsive" style="overflow-x:unset; padding: 0 1rem;">
                    <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                        <thead>
                            <tr>
                                <th style="min-width: 120px;">Name</th>
                                <th style="min-width: 100px;">Vendor</th>
                                <th style="min-width: 100px;">Category</th>
                                <th style="min-width: 80px;">Price</th>
                                <th style="min-width: 60px;">Stock</th>
                                <th style="min-width: 80px;">Status</th>
                                <th style="min-width: 90px;">Images</th>
                                <th style="min-width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.products.show', $product) }}'">
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->vendor?->shop_name }}</td>
                                <td>{{ $product->category?->name }}</td>
                                <td>{{ $product->price }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>{{ ucfirst($product->status) }}</td>
                                <td>
                                    @foreach($product->images->take(2) as $img)
                                    <img src="{{ asset('storage/' . ($img->image ?? $img->image_path)) }}" alt=""
                                        class="rounded border bg-white"
                                        style="max-height:40px; max-width:40px; object-fit:cover; margin-right:2px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                                    @endforeach
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                        class="btn product-action-btn btn-outline-primary btn-sm me-1" title="Edit">
                                        <i class="cil-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                        class="d-inline-block"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
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
                                <td colspan="8" class="text-center">No products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $products->links('pagination::bootstrap-5') }}
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
