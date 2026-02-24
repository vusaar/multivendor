<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Roles Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Roles</h5>
            <a href="{{ route('admin.roles.create') }}" class="btn action-btn btn-new mb-3"><i class="cil-plus"></i> New Role</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x:unset; padding: 1rem 1rem;">
                <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach($role->permissions as $permission)
                                        <span class="badge bg-secondary">{{ $permission->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm action-btn edit-btn"><i class="cil-pencil"></i></a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm action-btn delete-btn" style="margin:2px;"><i class="cil-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
