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
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-outline active product-action-btn btn-new"><i class="cil-plus"></i> New Vendor</a>
        </div>
        <div class="card-body text-secondary p-0">
            <div class="table-responsive" style="overflow-x:unset; padding: 1rem 1rem 1rem;">
                <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
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
                                    <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-secondary btn-sm action-btn edit-btn"><i class="cil-pencil"></i></a>
                                    <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this vendor?')" style="margin:5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn  btn-sm action-btn delete-btn"><i class="cil-trash" ></i></button>
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
