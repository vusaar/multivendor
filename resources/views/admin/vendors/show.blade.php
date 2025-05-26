<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Vendors </h5>
            <form class="row g-2" method="GET" action="">
                <div class="col-auto">
                    <input type="text" name="vendor_name" class="form-control" placeholder="Vendor Name" value="{{ request('vendor_name') }}">
                </div>
                <div class="col-auto">
                    <input type="text" name="admin_email" class="form-control" placeholder="Admin Email" value="{{ request('admin_email') }}">
                </div>
                <div class="col-auto">
                    <input type="text" name="city" class="form-control" placeholder="City" value="{{ request('city') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary"><i class="cil-search"></i> Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor Name</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Longitude</th>
                            <th>Latitude</th>
                            <th>Administrators</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->city }}</td>
                                <td>{{ $vendor->country }}</td>
                                <td>{{ $vendor->longitude }}</td>
                                <td>{{ $vendor->latitude }}</td>
                                <td>
                                    @if($vendor->users->count())
                                        <ul class="list-unstyled mb-0">
                                            @foreach($vendor->users as $user)
                                                <li><i class="cil-user"></i> {{ $user->name }} <span class="text-muted">({{ $user->email }})</span></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">No administrators</span>
                                    @endif
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
