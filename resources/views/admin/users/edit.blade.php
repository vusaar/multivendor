<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit User</h4>
                </div>
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Password <small class="text-lowercase fw-normal">(Leave blank to keep current)</small></label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg border-0 bg-light rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg border-0 bg-light rounded-3">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label for="roles" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Assign Roles</label>
                            <select name="roles[]" id="roles" class="form-select select2 border-0 bg-light rounded-3" multiple required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->contains($role->name) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                             Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
