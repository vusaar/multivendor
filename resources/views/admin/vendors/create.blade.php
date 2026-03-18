<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Vendor') }}
        </h2>
    </x-slot>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Create Vendor</h4>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.vendors.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label for="shop_name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Shop Name</label>
                                <input type="text" class="form-control form-control-lg border-0 bg-light rounded-3" id="shop_name" name="shop_name" value="{{ old('shop_name') }}" required placeholder="Enter shop name">
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Description</label>
                                <textarea class="form-control border-0 bg-light rounded-3" id="description" name="description" rows="3" placeholder="Tell us about the vendor">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="logo" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Shop Logo</label>
                                <div class="input-group">
                                    <input type="file" class="form-control border-0 bg-light rounded-start-3" id="logo" name="logo" accept="image/*">
                                </div>
                                <div class="form-text small">Recommended: Square image, min 200x200px.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Initial Status</label>
                                <select class="form-select form-select-lg border-0 bg-light rounded-3" id="status" name="status" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                        </div>

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--midnight-rgb), 0.02)">
                            <h6 class="fw-bold mb-3" style="color: var(--midnight)">Location Details</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="address" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Full Address</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="address" name="address" value="{{ old('address') }}" placeholder="Street, City, Country">
                                </div>
                                <div class="col-md-6">
                                    <label for="longitude" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Longitude</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="e.g. 36.8219">
                                </div>
                                <div class="col-md-6">
                                    <label for="latitude" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Latitude</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="e.g. -1.2921">
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                            <h6 class="fw-bold mb-3" style="color: var(--primary)">Administrator Assignment</h6>
                            <label for="user_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Select User</label>
                            <select class="form-select border-0 bg-white rounded-3" id="user_id" name="user_id" required>
                                <option value="">-- Choose an administrative user --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <div class="form-text small mt-2">This user will have full management access to the vendor shop.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3">
                            <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                                Create Vendor Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
