<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Details') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header">Product Details</div>
                    <div class="card-body">
                        <h4>{{ $product->name }}</h4>
                        <p><strong>Vendor:</strong> {{ $product->vendor?->name }}</p>
                        <p><strong>Category:</strong> {{ $product->category?->name }}</p>
                        <p><strong>Description:</strong> {{ $product->description }}</p>
                        <p><strong>Price:</strong> {{ $product->price }}</p>
                        <p><strong>Stock:</strong> {{ $product->stock }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($product->status) }}</p>
                        <div class="mb-3">
                            <strong>Images:</strong><br>
                            @foreach($product->images as $img)
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="" style="max-height:80px; max-width:80px;" class="me-2 mb-2">
                            @endforeach
                        </div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-warning">Edit</a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
