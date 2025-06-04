<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mb-4">
                    <div class="card-header">Create Product</div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="vendor_id" class="form-label">Vendor</label>
                                <select class="form-select" id="vendor_id" name="vendor_id">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="category_tree" class="form-label">Category</label>
                                <div id="category_breadcrumb" class="mb-2 text-secondary small"></div>
                                <div id="category_tree"></div>
                                <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id') }}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="images" class="form-label">Product Images</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                <div id="image-preview-list" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Product Variations</label>
                                <div id="variation-list">
                                    <div class="row g-2 mb-2 variation-row">
                                        <div class="col-md-4">
                                            <select class="form-select variation-attribute-select" name="variations[0][attribute_id]" required>
                                                <option value="">-- Select Attribute --</option>
                                                @foreach(App\Models\VariationAttribute::all() as $attr)
                                                    <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select variation-value-select" name="variations[0][value][]" multiple required disabled>
                                                <option value="">-- Select Value --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="variations[0][sku]" placeholder="SKU (optional)">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-variation-btn">&times;</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-variation-btn">Add Variation</button>
                            </div>
                            <button type="submit" class="btn btn-success">Create Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



<script>
    // Prepare category data for jsTree (up to 3 levels, with CoreUI icons)
    var categoryData = [];
    // Add a top-level parent node for all categories
    categoryData.push({
        id: 'all-categories',
        parent: '#',
        text: 'All Categories',
        type: 'folder',
        state: { opened: true, disabled: true }
    });
    @foreach($categories as $parent)
        categoryData.push({
            id: '{{ $parent->id }}',
            parent: 'all-categories',
            text: @json($parent->name),
            type: '{{ $parent->children->isEmpty() ? 'tag' : 'folder' }}',
            state: { @if(old('category_id') == $parent->id) selected: true @endif }
        });
        @foreach($parent->children as $child)
            categoryData.push({
                id: '{{ $child->id }}',
                parent: '{{ $parent->id }}',
                text: @json($child->name),
                type: '{{ $child->children->isEmpty() ? 'tag' : 'folder' }}',
                state: { @if(old('category_id') == $child->id) selected: true @endif }
            });
            @foreach($child->children as $grandchild)
                categoryData.push({
                    id: '{{ $grandchild->id }}',
                    parent: '{{ $child->id }}',
                    text: @json($grandchild->name),
                    type: 'tag',
                    state: { @if(old('category_id') == $grandchild->id) selected: true @endif }
                });
            @endforeach
        @endforeach
    @endforeach
    $(function() {
        $('#category_tree').jstree({
            core: {
                data: categoryData,
                multiple: false,
                check_callback: true
            },
            plugins: ['wholerow', 'types'],
            types: {
                folder: {
                    icon: 'cil-folder text-warning', // CoreUI folder icon
                },
                tag: {
                    icon: 'cil-tag text-info', // CoreUI tag icon
                }
            }
        });
        // Prevent selection of non-leaf nodes
        $('#category_tree').on('before.jstree', function(e, data) {
            if (data.func === 'select_node') {
                var node = $('#category_tree').jstree(true).get_node(data.args[0]);
                if (node.children.length > 0) {
                    e.preventDefault();
                }
            }
        });
        // Set initial selection
        $('#category_tree').on('ready.jstree', function() {
            var selected = $('#category_id').val();
            if (selected) {
                var node = $('#category_tree').jstree(true).get_node(selected);
                if (node && node.children.length === 0) {
                    $('#category_tree').jstree('select_node', selected);
                }
            }
        });
        // Update hidden input on selection
        $('#category_tree').on('changed.jstree', function(e, data) {
            var selected = data.selected[0] || '';
            var tree = $('#category_tree').jstree(true);
            var node = tree.get_node(selected);
            if (node && node.children.length === 0) {
                $('#category_id').val(selected);
                // Expand all parents of the selected node
                var parents = node.parents;
                parents.forEach(function(parentId) {
                    if (parentId !== '#') {
                        tree.open_node(parentId);
                    }
                });
            } else {
                $('#category_id').val('');
            }
        });
        // Expand/collapse parent node if it's selected (clicked)
        $('#category_tree').on('select_node.jstree', function(e, data) {
            var tree = $('#category_tree').jstree(true);
            var node = data.node;
            if (node.children.length > 0) {
                if (tree.is_open(node)) {
                    tree.close_node(node);
                } else {
                    tree.open_node(node);
                }
                // Deselect parent so it can't be selected
                tree.deselect_node(node);
            }
        });
        // Helper to build breadcrumb from node
        function buildBreadcrumb(tree, node) {
            if (!node) return '';
            var names = [];
            var current = node;
            while (current && current.id !== '#' && current.id !== 'all-categories') {
                names.unshift(current.text);
                if (!current.parents || !current.parents.length) break;
                current = tree.get_node(current.parent);
            }
            return names.join(' / ');
        }
        // Show breadcrumb for initial selection
        function updateBreadcrumb() {
            var tree = $('#category_tree').jstree(true);
            var selected = $('#category_id').val();
            var node = tree.get_node(selected);
            var breadcrumb = buildBreadcrumb(tree, node);
            $('#category_breadcrumb').text(breadcrumb);
        }
        // Update breadcrumb on tree ready and selection change
        $('#category_tree').on('ready.jstree', function() {
            // ...existing code...
            updateBreadcrumb();
        });
        $('#category_tree').on('changed.jstree', function(e, data) {
            // ...existing code...
            updateBreadcrumb();
        });
    });

    // Image preview for product images with delete icon
    document.getElementById('images').addEventListener('change', function(event) {
        var preview = document.getElementById('image-preview-list');
        preview.innerHTML = '';
        var files = event.target.files;
        if (files) {
            Array.from(files).forEach(function(file, idx) {
                if (!file.type.match('image.*')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    var wrapper = document.createElement('div');
                    wrapper.className = 'position-relative';
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'rounded border';
                    img.style.height = '64px';
                    img.style.width = '64px';
                    img.style.objectFit = 'cover';
                    img.title = file.name;
                    // Delete icon
                    var del = document.createElement('span');
                    del.className = 'position-absolute top-0 end-0 translate-middle badge rounded-pill pointer';
                    del.style.cursor = 'pointer';
                    del.style.zIndex = '2';
                    del.style.background = 'rgba(128,128,128,0.6)'; // gray, 60% translucent
                    // Optionally, set color to white for the X
                    // del.style.color = '#fff';
                    del.innerHTML = '&times;';
                    del.title = 'Remove';
                    del.onclick = function() {
                        // Remove the file from the input
                        var dt = new DataTransfer();
                        var input = document.getElementById('images');
                        Array.from(input.files).forEach(function(f, i) {
                            if (i !== idx) dt.items.add(f);
                        });
                        input.files = dt.files;
                        // Remove the preview
                        wrapper.remove();
                        // Trigger change event to re-render previews
                        var evt = new Event('change', { bubbles: true });
                        input.dispatchEvent(evt);
                    };
                    wrapper.appendChild(img);
                    wrapper.appendChild(del);
                    preview.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Variation attribute -> value dynamic loading
    function loadVariationValues(attributeId, valueSelect, selectedValues = []) {
        if (!attributeId) {
            valueSelect.innerHTML = '<option value="">-- Select Value --</option>';
            valueSelect.disabled = true;
            $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
            return;
        }
        fetch('/admin/variation-attributes/' + attributeId + '/values')
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- Select Value --</option>';
                data.forEach(function(val) {
                    let selected = selectedValues && selectedValues.includes(val.value) ? ' selected' : '';
                    options += `<option value="${val.value}"${selected}>${val.value}</option>`;
                });
                valueSelect.innerHTML = options;
                valueSelect.disabled = false;
                $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
            });
    }

    function bindVariationAttributeEvents() {
        document.querySelectorAll('.variation-attribute-select').forEach(function(select) {
            select.onchange = function() {
                const row = select.closest('.variation-row');
                const valueSelect = row.querySelector('.variation-value-select');
                loadVariationValues(select.value, valueSelect, []);
            };
        });
        document.querySelectorAll('.variation-value-select').forEach(function(sel) {
            $(sel).select2({ theme: 'bootstrap-5', width: '100%' });
        });
    }

    // Initial bind
    document.addEventListener('DOMContentLoaded', function() {
        bindVariationAttributeEvents();
    });

    // Add variation row with dynamic value select
    let variationIndex = 1;
    document.getElementById('add-variation-btn').addEventListener('click', function() {
        var list = document.getElementById('variation-list');
        var row = document.createElement('div');
        row.className = 'row g-2 mb-2 variation-row';
        row.innerHTML = `
            <div class="col-md-4">
                <select class="form-select variation-attribute-select" name="variations[${variationIndex}][attribute_id]" required>
                    <option value="">-- Select Attribute --</option>
                    @foreach(App\Models\VariationAttribute::all() as $attr)
                        <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select variation-value-select" name="variations[${variationIndex}][value][]" multiple required disabled>
                    <option value="">-- Select Value --</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="variations[${variationIndex}][sku]" placeholder="SKU (optional)">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-variation-btn">&times;</button>
            </div>
        `;
        list.appendChild(row);
        bindVariationAttributeEvents();
        variationIndex++;
    });
    document.getElementById('variation-list').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variation-btn')) {
            e.target.closest('.variation-row').remove();
        }
    });
</script>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush




