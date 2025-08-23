<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Permission Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Permissions</h5>
            <a href="{{ route('admin.permissions.create') }}" class="btn action-btn btn-new mb-3"><i class="cil-plus"></i> New Permission</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x:unset; padding: 1rem 1rem;">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm action-btn edit-btn"> Edit</a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this permission?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm action-btn delete-btn"><i class="cil-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">No permissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $permissions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
