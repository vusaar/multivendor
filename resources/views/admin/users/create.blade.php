<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create User') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Create User</h4>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name') }}" required placeholder="e.g. John Doe">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('email') }}" required placeholder="john@example.com">
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg border-0 bg-light rounded-3" required placeholder="Minimum 8 characters">
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg border-0 bg-light rounded-3" required placeholder="Repeat password">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                             Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
