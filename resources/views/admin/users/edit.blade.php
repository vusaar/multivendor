<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Edit User</h5>
        </div>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="roles" class="form-label">Assign Role</label>
                    <select name="roles[]" id="roles" class="form-select select2" multiple required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->contains($role->name) ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple roles.</small>
                </div>
            </div>
            <div class="card-footer text-start">
                
                <button type="submit" class="btn action-btn btn-save"></i> Update</button>
                <a href="{{ route('admin.users.index') }}" class="btn action-btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && $.fn.select2) {
            $('#roles').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Select roles',
                allowClear: true
            });
        } else {
            console.error('Select2 or jQuery not loaded');
        }
    });
</script>
@endpush
</x-app-layout>
