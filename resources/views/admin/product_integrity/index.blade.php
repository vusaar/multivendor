<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Integrity Check') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Unlinked Products</strong>
                            <span class="badge bg-danger ms-2">{{ $unlinkedProducts->total() }}</span>
                        </div>
                        <div>
                           <form action="{{ route('admin.product-integrity.auto-fix') }}" method="POST" onsubmit="return confirm('This will create new Master Products for all unlinked items based on their names. Continue?');">
                               @csrf
                               <button type="submit" class="btn btn-sm btn-primary">Auto-Fix All (Create New Masters)</button>
                           </form>
                        </div>
                    </div>
                    <div class="card-body">
                         @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($unlinkedProducts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product Name</th>
                                            <th>Vendor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unlinkedProducts as $product)
                                            <tr>
                                                <td>{{ $product->id }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-xs btn-info">Edit & Link</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $unlinkedProducts->appends(['desync_page' => request('desync_page')])->links() }}
                            </div>
                        @else
                            <div class="alert alert-success m-0">
                                All products are linked to a Master Product!
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
             <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Potential Name Desyncs</strong>
                        <small class="text-muted">(Product Name != Master Product Name)</small>
                        <span class="badge bg-warning ms-2">{{ $desyncedProducts->total() }}</span>
                    </div>
                    <div class="card-body">
                         @if($desyncedProducts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Current Product Name</th>
                                            <th>Linked Master Product</th>
                                            <th>Vendor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($desyncedProducts as $product)
                                            <tr>
                                                <td>{{ $product->id }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $product->master_name }}</span>
                                                </td>
                                                <td>{{ $product->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-xs btn-primary">Edit</a>
                                                        <form action="{{ route('admin.product-integrity.detach', $product->id) }}" method="POST" onsubmit="return confirm('Detach this product from {{ $product->master_name }}?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-danger">Detach</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                             <div class="mt-3">
                                {{ $desyncedProducts->appends(['unlinked_page' => request('unlinked_page')])->links() }}
                            </div>
                        @else
                             <div class="alert alert-success m-0">
                                No name mismatches found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
