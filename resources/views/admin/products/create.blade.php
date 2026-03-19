<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/product-management.css') }}">
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Product') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4 product-form-container">
        <form id="product-create-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="pm-layout">
                <!-- Sticky Sidebar Navigation -->
                <aside class="pm-sidebar d-none d-lg-block">
                    <div class="pm-side-nav">
                        <a href="#section-media" class="pm-side-link active">
                            <i class="cil-image"></i> Media
                        </a>
                        <a href="#section-general" class="pm-side-link">
                            <i class="cil-info"></i> Basic Info
                        </a>
                        <a href="#section-inventory" class="pm-side-link">
                            <i class="cil-money"></i> Pricing & Stock
                        </a>
                        <a href="#section-variations" class="pm-side-link">
                            <i class="cil-layers"></i> Variations
                        </a>
                        
                        <hr class="my-3 opacity-10">
                        
                        <div class="px-3 pb-2">
                             <button type="submit" class="pm-btn pm-btn-primary w-100 mb-2">Publish Product</button>
                             <a href="{{ route('admin.products.index') }}" class="pm-btn pm-btn-secondary w-100">Discard</a>
                        </div>
                    </div>
                </aside>

                <!-- Scrollable Content -->
                <main class="pm-content">
                    <!-- Section: Media -->
                    <section id="section-media" class="pm-card">
                        <div class="pm-card-header">
                            <i class="cil-image"></i> Product Media
                        </div>
                        <div class="pm-card-body">
                            <div class="pm-upload-zone" id="image-upload-zone">
                                <input type="file" name="images[]" id="images-main" multiple accept="image/*" class="d-none">
                                <div class="mb-3">
                                    <i class="cil-cloud-upload" style="font-size: 2rem; color: var(--pm-primary);"></i>
                                </div>
                                <h6 class="fw-bold">Drag and drop images here</h6>
                                <p class="text-muted small">or click to browse from your computer</p>
                            </div>
                            <div id="main-previews" class="pm-preview-container"></div>
                        </div>
                    </section>

                    <!-- Section: General Info -->
                    <section id="section-general" class="pm-card">
                        <div class="pm-card-header">
                            <i class="cil-info"></i> Global Details
                        </div>
                        <div class="pm-card-body">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label class="pm-label">Category</label>
                                        <input type="text" id="category-search" class="pm-input mb-2" placeholder="Search categories...">
                                        <div id="category_breadcrumb" class="small mb-2 fw-bold" style="color:var(--pm-primary)"></div>
                                        <div id="category_tree" class="border rounded bg-white" style="max-height: 200px; overflow-y: auto;"></div>
                                        <input type="hidden" name="category_id" id="category_id">
                                    </div>
                                    <div class="mb-4">
                                        <label class="pm-label">Product Name</label>
                                        <select class="select2-tags pm-select" name="name" required>
                                            <option value="">Type name or search...</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="pm-label">Description</label>
                                        <textarea name="description" class="pm-textarea" rows="6" placeholder="Tell customers about this product..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-4">
                                        <label class="pm-label">Vendor</label>
                                        <select name="vendor_id" class="pm-select">
                                            <option value="">Select Vendor</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="pm-label">Brand</label>
                                        <select name="brand_id" class="pm-select">
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section: Pricing & Inventory -->
                    <section id="section-inventory" class="pm-card">
                        <div class="pm-card-header">
                            <i class="cil-money"></i> Pricing & Stock
                        </div>
                        <div class="pm-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="pm-label">Base Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="price" class="pm-input" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="pm-label">Initial Stock</label>
                                    <input type="number" name="stock" class="pm-input" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="pm-label">Status</label>
                                    <select name="status" class="pm-select">
                                        <option value="active">Active (Visible)</option>
                                        <option value="inactive">Inactive (Hidden)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section: Variations -->
                    <section id="section-variations" class="pm-card">
                        <div class="pm-card-header d-flex justify-content-between">
                            <span><i class="cil-layers"></i> Product Variations</span>
                            <button type="button" id="open-generator-btn" class="pm-btn pm-btn-secondary btn-sm">
                                <i class="cil-bolt"></i> Batch Generator
                            </button>
                        </div>
                        <div class="pm-card-body">
                            <!-- Generator Section (Persistent inside variations card) -->
                            <div id="generator-modal" class="collapse border-bottom mb-4 pb-4">
                                <div class="bg-light p-4 rounded-3">
                                    <h6 class="fw-bold mb-3">Batch Variation Generator</h6>
                                    <div id="generator-rows">
                                        <div class="gen-attr-row row g-3 mb-2">
                                            <div class="col-md-5">
                                                <select class="pm-select gen-attr-select">
                                                    <option value="">Select Attribute</option>
                                                    @foreach(App\Models\VariationAttribute::all() as $attr)
                                                        <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="pm-select gen-value-select" multiple placeholder="Select values">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm p-0 mb-3" onclick="addGenRow()">+ Add Another Attribute</button>
                                    
                                    <div class="row g-3 items-center">
                                        <div class="col-md-4">
                                            <input type="number" id="gen-price" class="pm-input" placeholder="Price for combinations">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" id="gen-stock" class="pm-input" placeholder="Stock for combinations">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" id="run-generator-btn" class="pm-btn pm-btn-primary w-100">Generate Variations</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="variation-container">
                                <!-- Variations injected here -->
                            </div>
                            <button type="button" id="add-variation-btn" class="pm-btn pm-btn-secondary w-100 mt-3 d-flex align-items-center justify-content-center">
                                <i class="cil-plus"></i> Add Manual Variation
                            </button>
                        </div>
                    </section>

                    <div class="d-lg-none mt-4 text-center">
                         <button type="submit" class="pm-btn pm-btn-primary px-5 w-100 mb-2">Publish Product</button>
                         <a href="{{ route('admin.products.index') }}" class="pm-btn pm-btn-secondary w-100">Discard Changes</a>
                    </div>
                </main>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/product-form.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Scrollspy logic for sidebar
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.3
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        document.querySelectorAll('.pm-side-link').forEach(link => {
                            link.classList.toggle('active', link.getAttribute('href') === `#${id}`);
                        });
                    }
                });
            }, observerOptions);

            document.querySelectorAll('section[id]').forEach(section => {
                observer.observe(section);
            });

            // Init ProductForm
            const attributes = @json(App\Models\VariationAttribute::with('values')->get());
            
            const categoryData = [];
            categoryData.push({
                id: 'all-categories', parent: '#', text: 'All Categories', type: 'folder', state: { opened: true, disabled: true }
            });
            @foreach($categories as $parent)
                categoryData.push({
                    id: '{{ $parent->id }}', parent: 'all-categories', text: @json($parent->name), type: '{{ $parent->children->isEmpty() ? 'tag' : 'folder' }}'
                });
                @foreach($parent->children as $child)
                    categoryData.push({
                        id: '{{ $child->id }}', parent: '{{ $parent->id }}', text: @json($child->name), type: '{{ $child->children->isEmpty() ? 'tag' : 'folder' }}'
                    });
                    @foreach($child->children as $grandchild)
                        categoryData.push({
                            id: '{{ $grandchild->id }}', parent: '{{ $child->id }}', text: @json($grandchild->name), type: 'tag'
                        });
                    @endforeach
                @endforeach
            @endforeach

            ProductForm.init({
                attributes: attributes,
                categoryData: categoryData,
                initialVariationIndex: 0
            });

            // Generator Helper
            window.addGenRow = function() {
                const row = document.querySelector('.gen-attr-row').cloneNode(true);
                row.querySelector('.gen-value-select').innerHTML = '';
                row.querySelector('.gen-attr-select').value = '';
                $(row.querySelector('.gen-value-select')).select2({ theme: 'bootstrap-5', width: '100%' });
                document.getElementById('generator-rows').appendChild(row);
                bindGenEvents(row);
            };

            function bindGenEvents(row) {
                const attrSelect = row.querySelector('.gen-attr-select');
                const valueSelect = row.querySelector('.gen-value-select');
                attrSelect.onchange = () => {
                    const attr = attributes.find(a => a.id == attrSelect.value);
                    if (attr) {
                        let html = '';
                        attr.values.forEach(v => html += `<option value="${v.id}">${v.value}</option>`);
                        valueSelect.innerHTML = html;
                        $(valueSelect).trigger('change');
                    }
                };
            }

            $('.gen-value-select').select2({ theme: 'bootstrap-5', width: '100%' });
            bindGenEvents(document.querySelector('.gen-attr-row'));

            $('.select2-tags').select2({ theme: 'bootstrap-5', width: '100%', tags: true });
        });
    </script>
</x-app-layout>
