<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vendors Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Vendors</h5>
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-outline active"><i class="cil-plus"></i> New Vendor</a>
        </div>
        <div class="card-body text-secondary p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-top mb-0">
                    <thead class="table">
                        <tr>
                            <th>Name</th>
                            <th>Admins</th>
                            <th>Address</th>
                            <th>Longitude</th>
                            <th>Latitude</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                               
                                <td>{{ $vendor->shop_name }} <br><small class="blockquote-footer"><small>{{$vendor->description}}</small></small></td>
                                <td>{{ $vendor->user->name }}</td>
                                <td>{{ $vendor->address }} <br><small class="blockquote-footer"><small>{{ $vendor->city }}, {{ $vendor->country }}</small></small></td>
                                <td>{{ $vendor->longitude }}</td>
                                <td>{{ $vendor->latitude }}</td>
                                <td>{{ $vendor->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-secondary btn-sm"><i class="cil-pencil"></i> Edit</a>
                                    <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this vendor?')" style="margin:5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-dark btn-sm"><i class="cil-trash" ></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No vendors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $vendors->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
