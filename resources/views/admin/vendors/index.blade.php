<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vendors Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="glass-card mb-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
            <h4 class="mb-0 fw-bold" style="color:var(--midnight)">Vendors</h4>
            @can('create', App\Models\Vendor::class)
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
                <i class="cil-plus"></i> New Vendor
            </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Shop Information</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted">Admin</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted">Location</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted text-center">Coordinates</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted">Created</th>
                            <th class="pe-4 border-0 text-uppercase small fw-bold text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $vendor->shop_name }}</div>
                                    <div class="small text-muted">{{ Str::limit($vendor->description, 50) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.75rem; color: var(--midnight); font-weight: 700;">
                                            {{ strtoupper(substr($vendor->user->name, 0, 1)) }}
                                        </div>
                                        <span class="text-dark">{{ $vendor->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark">{{ $vendor->city }}, {{ $vendor->country }}</div>
                                    <div class="small text-muted">{{ Str::limit($vendor->address, 30) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-muted border font-monospace px-2">
                                        {{ number_format($vendor->latitude, 4) }}, {{ number_format($vendor->longitude, 4) }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $vendor->created_at->format('M d, Y') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        @can('delete', $vendor)
                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this vendor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                                <i class="cil-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No vendors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 p-4">
            {{ $vendors->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
