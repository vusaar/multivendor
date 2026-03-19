<x-app-layout>
    <x-slot name="header">
        
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color:var(--midnight)">Products</h4>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="cil-plus"></i> Add Product
                </a>
            </div>
            <div class="card-body p-0">
                <div class="px-4 pb-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Product name...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase text-muted">Vendor</label>
                            <select name="vendor_id" class="form-select">
                                <option value="">All Vendors</option>
                                @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->shop_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase text-muted">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" type="submit">
                                <i class="cil-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="cil-reload"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Product</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Vendor</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Category</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Price</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Stock</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Status</th>
                                <th class="pe-4 border-0 text-uppercase small fw-bold text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('admin.products.show', $product) }}'">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($product->images->first())
                                            <img src="{{ asset('storage/' . ($product->images->first()->image ?? $product->images->first()->image_path)) }}" 
                                                 class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                            @else
                                            <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                <i class="cil-image text-muted"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $product->name }}</div>
                                            <div class="small text-muted">{{ $product->brand?->name ?? 'No Brand' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $product->vendor?->shop_name }}</span></td>
                                <td>{{ $product->category?->name }}</td>
                                <td class="fw-bold text-dark">${{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->stock <= 5)
                                    <span class="text-danger fw-bold"><i class="cil-warning"></i> {{ $product->stock }}</span>
                                    @else
                                    <div class="d-flex align-items-center gap-2">
                                        {{ $product->stock }}
                                        <i class="cil-check-alt text-emerald small" title="In Stock"></i>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status == 'active')
                                    <span class="badge-emerald">
                                        <i class="cil-circle" style="font-size: 0.5rem"></i> Active
                                    </span>
                                    @else
                                    <span class="badge bg-secondary rounded-pill px-3">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}"
                                            class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                            class="d-inline-block"
                                            onsubmit="return confirm('Are you sure you want to delete this product?');">
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
                                <td colspan="7" class="text-center py-5 text-muted">No products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-4">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
