<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Users</h5>
            <a href="{{ route('admin.users.create') }}" class="btn action-btn btn-new mb-3"><i class="cil-plus"></i> New User</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x:unset; padding: 1rem 1rem;">
                <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role(s)</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if(method_exists($user, 'getRoleNames'))
                                        @foreach($user->getRoleNames() as $role)
                                            <span class="badge bg-secondary text-light">{{ $role }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm action-btn edit-btn"><i class="cil-pencil"></i> </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm action-btn delete-btn"><i class="cil-trash"></i> </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
