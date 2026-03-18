<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-dark">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100">
                        <div class="stat-label">Total Products</div>
                        <div class="stat-value text-crimson">11</div>
                        <div class="stat-trend trend-up">
                            <i class="cil-arrow-top"></i> +12% this month
                        </div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-library"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100">
                        <div class="stat-label">Active Vendors</div>
                        <div class="stat-value">4</div>
                        <div class="stat-trend trend-up">
                            <i class="cil-arrow-top"></i> +1 new since yesterday
                        </div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-house"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100">
                        <div class="stat-label">Product Categories</div>
                        <div class="stat-value">34</div>
                        <div class="stat-trend text-emerald">
                            <i class="cil-check-alt"></i> Fully Synchronized
                        </div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-speedometer"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h4 class="fw-bold mb-4" style="color:var(--midnight)">Quick Actions</h4>
            <div class="row g-4">
                <div class="col-md-3">
                    <a href="{{ route('admin.products.create') }}" class="text-decoration-none">
                        <div class="glass-card p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                            <div class="mb-3 p-3 rounded-circle" style="background: rgba(225, 29, 72, 0.1); color: var(--primary);">
                                <i class="cil-plus" style="font-size: 1.5rem;"></i>
                            </div>
                            <span class="fw-bold text-dark">Add Product</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.vendors.index') }}" class="text-decoration-none">
                        <div class="glass-card p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                            <div class="mb-3 p-3 rounded-circle" style="background: rgba(15, 23, 42, 0.1); color: var(--midnight);">
                                <i class="cil-house" style="font-size: 1.5rem;"></i>
                            </div>
                            <span class="fw-bold text-dark">Manage Shops</span>
                        </div>
                    </a>
                </div>
                <!-- Add more as needed -->
            </div>
        </div>
    </div>
</x-app-layout>
