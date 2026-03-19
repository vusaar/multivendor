<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Role') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Create Role</h4>
                </div>
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Role Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name') }}" required placeholder="e.g. Manager">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-600 small text-uppercase tracking-wider text-muted mb-3">Assign Permissions</label>
                            <div class="row g-3">
                                @foreach($permissions as $permission)
                                    <div class="col-md-4">
                                        <div class="form-check custom-check">
                                            <input class="form-check-input border-0" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                            <label class="form-check-label fw-500" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                             Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-check .form-check-input {
        background-color: #f3f4f6;
        width: 1.25em;
        height: 1.25em;
        margin-top: 0.125em;
    }
    .custom-check .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .fw-500 { font-weight: 500; }
</style>
</x-app-layout>
