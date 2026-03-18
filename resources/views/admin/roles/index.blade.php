<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Roles Listing') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Roles</h4>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="cil-plus"></i> New Role
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Role Name</th>
                                <th>Permissions</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-dark">{{ $role->name }}</span></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($role->permissions as $permission)
                                            <span class="badge bg-soft-primary text-primary">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this role?')">
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
                                <td colspan="3" class="text-center py-5 text-muted">No roles found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4 text-end">
                {{ $roles->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .bg-soft-primary {
            background-color: rgba(225, 29, 72, 0.1) !important;
            color: var(--primary) !important;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.4em 0.8em;
            border-radius: 6px;
        }
    </style>
    @endpush
</x-app-layout>
