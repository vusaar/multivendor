<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Vendor') }}
        </h2>
    </x-slot>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit Vendor</h4>
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
                    <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label for="shop_name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Shop Name</label>
                                <input type="text" class="form-control form-control-lg border-0 bg-light rounded-3" id="shop_name" name="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Description</label>
                                <textarea class="form-control border-0 bg-light rounded-3" id="description" name="description" rows="3">{{ old('description', $vendor->description) }}</textarea>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="logo" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Shop Logo</label>
                                @if($vendor->logo)
                                    <div class="mb-3 p-2 bg-white rounded-3 border d-inline-block">
                                        <img src="{{ asset('storage/' . $vendor->logo) }}" alt="Logo" style="max-height: 80px; border-radius: 8px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control border-0 bg-light rounded-3" id="logo" name="logo" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Account Status</label>
                                @if(auth()->user()->hasRole('super.admin'))
                                    <select class="form-select form-select-lg border-0 bg-light rounded-3" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $vendor->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ old('status', $vendor->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $vendor->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                @else
                                    <div class="form-control form-control-lg border-0 bg-light rounded-3 d-flex align-items-center">
                                        @if($vendor->status == 'approved')
                                            <span class="badge bg-emerald text-white px-3 py-2">Approved</span>
                                        @elseif($vendor->status == 'pending')
                                            <span class="badge bg-warning text-dark px-3 py-2">Pending Review</span>
                                        @else
                                            <span class="badge bg-danger text-white px-3 py-2">Rejected</span>
                                        @endif
                                        <input type="hidden" name="status" value="{{ $vendor->status }}">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--midnight-rgb), 0.02)">
                            <h6 class="fw-bold mb-3" style="color: var(--midnight)">Location Details</h6>
                            <div class="row g-3">
                                <div class="col-md-12 mb-2">
                                    <label for="address" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Address</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="address" name="address" value="{{ old('address', $vendor->address) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label fw-600 small text-uppercase tracking-wider text-muted">City</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="city" name="city" value="{{ old('city', $vendor->city) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="country" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Country</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="country" name="country" value="{{ old('country', $vendor->country) }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="longitude" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Long</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="longitude" name="longitude" value="{{ old('longitude', $vendor->longitude) }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="latitude" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Lat</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="latitude" name="latitude" value="{{ old('latitude', $vendor->latitude) }}">
                                </div>
                            </div>
                        </div>

                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--midnight-rgb), 0.02)">
                            <h6 class="fw-bold mb-3" style="color: var(--midnight)">Contact & Social Media</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Phone Number</label>
                                    <input type="text" class="form-control border-0 bg-white rounded-3" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}" placeholder="+1 234 567 890">
                                </div>
                                <div class="col-md-6">
                                    <label for="website" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Website URL</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="website" name="website" value="{{ old('website', $vendor->website) }}" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="social_fb" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Facebook</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_fb" name="social_links[facebook]" value="{{ old('social_links.facebook', $vendor->social_links['facebook'] ?? '') }}" placeholder="Facebook Profile URL">
                                </div>
                                <div class="col-md-4">
                                    <label for="social_ig" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Instagram</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_ig" name="social_links[instagram]" value="{{ old('social_links.instagram', $vendor->social_links['instagram'] ?? '') }}" placeholder="Instagram Profile URL">
                                </div>
                                <div class="col-md-4">
                                    <label for="social_x" class="form-label fw-600 small text-uppercase tracking-wider text-muted">X (Twitter)</label>
                                    <input type="url" class="form-control border-0 bg-white rounded-3" id="social_x" name="social_links[x]" value="{{ old('social_links.x', $vendor->social_links['x'] ?? '') }}" placeholder="X Profile URL">
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->hasRole('super.admin'))
                        <div class="glass-panel p-4 mb-4 rounded-4" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                            <h6 class="fw-bold mb-3" style="color: var(--primary)">Change Administrator</h6>
                            <label for="user_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Select User</label>
                            <select class="form-select border-0 bg-white rounded-3 shadow-sm" id="user_id" name="user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $vendor->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small mt-2">Re-assigning the administrator will transfer all store management rights to the selected user.</div>
                        </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ $vendor->user_id }}">
                        @endif

                        <div class="d-flex justify-content-end gap-3 pt-3">
                            <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
