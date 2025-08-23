<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Role') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Create Role</h5>
        </div>
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <div class="row">
                        @foreach($permissions as $permission)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card-footer text-start">
                
                <button type="submit" class="btn action-btn btn-save"> Create</button>

                <a href="{{ route('admin.roles.index') }}" class="btn action-btn btn-cancel">Cancel</a>

            </div>
        </form>
    </div>
</div>
</x-app-layout>
