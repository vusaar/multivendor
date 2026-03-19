<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Listing') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Users</h4>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="cil-plus"></i> New User
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Role(s)</th>
                                <th>Created At</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(method_exists($user, 'getRoleNames'))
                                        @foreach($user->getRoleNames() as $role)
                                            <span class="badge badge-crimson">{{ $role }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td><span class="text-muted small">{{ $user->created_at->format('M d, Y') }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this user?')">
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
                                <td colspan="4" class="text-center py-5 text-muted">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            background: rgba(225, 29, 72, 0.05);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            border: 1px solid var(--crimson-glass-border);
        }
    </style>
    @endpush
</x-app-layout>
