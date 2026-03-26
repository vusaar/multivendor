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

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--midnight-rgb), 0.02)">
                            <h6 class="fw-bold mb-3" style="color: var(--midnight)">Contact & Social Media</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Phone Number</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 890">
                                </div>
                                <div class="col-md-6">
                                    <label for="website" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Website URL</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="social_fb" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Facebook</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_fb" name="social_links[facebook]" value="{{ old('social_links.facebook') }}" placeholder="Facebook Profile URL">
                                </div>
                                <div class="col-md-4">
                                    <label for="social_ig" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Instagram</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_ig" name="social_links[instagram]" value="{{ old('social_links.instagram') }}" placeholder="Instagram Profile URL">
                                </div>
                                <div class="col-md-4">
                                    <label for="social_x" class="form-label fw-600 small text-uppercase tracking-wider text-muted">X (Twitter)</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_x" name="social_links[x]" value="{{ old('social_links.x') }}" placeholder="X Profile URL">
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="color: var(--primary)">Administrator Assignment</h6>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" id="create_new_user" name="create_new_user" value="1" {{ old('create_new_user') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-muted" for="create_new_user">Create New User Instead</label>
                                </div>
                            </div>

                            <div id="existing_user_section" class="{{ old('create_new_user') ? 'd-none' : '' }}">
                                <label for="user_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Select Existing User</label>
                                <select class="form-select border-0 bg-white rounded-3" id="user_id" name="user_id">
                                    <option value="">-- Choose an administrative user --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text small mt-2">Only users with the 'vendor.admin' role are listed here.</div>
                            </div>

                            <div id="new_user_section" class="{{ old('create_new_user') ? '' : 'd-none' }}">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="new_admin_name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Full Name</label>
                                        <input type="text" class="form-control border-0 bg-white rounded-3" id="new_admin_name" name="new_admin_name" value="{{ old('new_admin_name') }}" placeholder="Admin's full name">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="new_admin_email" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Email Address</label>
                                        <input type="email" class="form-control border-0 bg-white rounded-3" id="new_admin_email" name="new_admin_email" value="{{ old('new_admin_email') }}" placeholder="admin@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="new_admin_password" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Password</label>
                                        <input type="password" class="form-control border-0 bg-white rounded-3" id="new_admin_password" name="new_admin_password" placeholder="Min. 8 characters">
                                    </div>
                                </div>
                                <div class="form-text small mt-2 text-primary">A new user will be created and automatically assigned the 'vendor.admin' role.</div>
                            </div>
                        </div>

                        <script>
                            document.getElementById('create_new_user').addEventListener('change', function() {
                                const isNew = this.checked;
                                document.getElementById('existing_user_section').classList.toggle('d-none', isNew);
                                document.getElementById('new_user_section').classList.toggle('d-none', !isNew);
                                
                                // Toggle 'required' attributes to help browser validation
                                document.getElementById('user_id').required = !isNew;
                                document.getElementById('new_admin_name').required = isNew;
                                document.getElementById('new_admin_email').required = isNew;
                                document.getElementById('new_admin_password').required = isNew;
                            });
                        </script>

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
