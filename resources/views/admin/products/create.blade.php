<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                            <div class="mb-6">

                               <div class="row g-3 mb-3">
                                      
                                  <div class="col-md-12 col-xs-12">
                                     <label for="vendor_id" class="form-label">Vendor</label>
                                     <select class="form-select" id="vendor_id" name="vendor_id">
                                         <option value="">-- Select Vendor --</option>
                                         @foreach($vendors as $vendor)
                                             <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ?      'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                         @endforeach
                                     </select>
                                  </div> 

                               </div>
                               
                               <div class="row g-3 mb-3">
                                  <div class="col-md-7 col-xs-12">
                                    <label for="name" class="form-label">Item / Product Name</label>
                                    <select class="form-select select2-tags" id="name" name="name" required>
                                        <option value="">-- Type to search or create --</option>
                                        @if(old('name'))
                                            <option value="{{ old('name') }}" selected>{{ old('name') }}</option>
                                        @endif
                                    </select>
                                  </div>
  
                                  <div class="col-md-5 col-xs-12">
                                     <label for="brand_id" class="form-label">Brand</label>
                                     <select  class="form-select" id="brand_id" name="brand_id" value="{{ old('name') }} "   required>
                                         <option value="">-- Select Brand -- </option>
                                         @foreach($brands as $brand)
                                             <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ?      'selected' : '' }}>{{ $brand->name }}</option>
                                         @endforeach
                                     </select>
                                  </div>
                               </div>

                                
                            </div>


                            <div class="mb-3">
                                <div class="row g-3">

                                  

                                  <div class="col-md-6 col-xs-12">

                                      <label for="category_tree" class="form-label">Category</label>
                                      <div id="category_breadcrumb" class="mb-2 text-secondary small"></div>
                                      <div id="category_tree" style="max-height:200px;overflow:auto; border:1px solid #cfd4de; border-radius:6px; padding:5px;"></div>
                                      <input type="hidden" name="category_id" id="category_id" value="{{ old      ('category_id') }}">

                                  </div> 
                                  
                                  
                                   <div class="col-md-6 col-xs-12 ">
                                   <label for="description" class="form-label">Description</label>
                                   <textarea class="form-control mt-3" id="description" name="description" style="min-height:200px;overflow:auto; border:1px solid #cfd4de; border-radius:6px; padding:5px;">{{ old('description') }}</textarea>
                                    </div> 

                                </div>
                            </div>


                            <div class="row g-2 mb-3">

                              <div class="col-md-6 col-xs-12">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
                              </div> 
                            
                              <div class="col-md-6 col-xs-12">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}" required>
                              </div>

                            </div>





                            <div class="row g-2 mb-3">

                               <div class="col-md-3 col-xs-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                               </div>
                            
                               <div class="col-md-9 col-xs-8">
                                <label for="images" class="form-label">Product Images</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                <div id="image-preview-list" class="mt-2 d-flex flex-wrap gap-2"></div>
                               </div>
                            
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Product Variations</label>
                                <div id="variation-matrix-list">
                                    <div class="row g-2 mb-2 variation-matrix-row">
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" name="variation_matrix[0][sku]" placeholder="SKU (optional)">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" name="variation_matrix[0][price]" placeholder="Price" step="0.01">
                                        </div>
                                        <div class="col-md-1">
                                            <input type="number" class="form-control" name="variation_matrix[0][stock]" placeholder="Stock">
                                        </div>
                                        <div class="col-md-1 d-flex flex-column align-items-center">
                                            <input type="file" class="form-control mb-1 variation-image-input" name="variation_matrix[0][image]" accept="image/*">
                                            <button type="button" class="btn product-action-btn btn-outline-danger btn-sm remove-variation-matrix-btn" title="Remove Variation">&times;</button>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="variation-attributes-group">
                                                <div class="row g-1 mb-1 variation-attribute-value-pair">
                                                    <div class="col-md-5">
                                                        <select class="form-select variation-attribute-select" name="variation_matrix[0][attributes][0][attribute_id]" required>
                                                            <option value="">-- Select Attribute --</option>
                                                            @foreach(App\Models\VariationAttribute::all() as $attr)
                                                                <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select variation-value-select" name="variation_matrix[0][attributes][0][value_id][]" multiple required disabled>
                                                            <option value="">-- Select Value --</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-center">
                                                        <button type="button" class="btn product-action-btn btn-outline-danger btn-sm remove-attribute-value-pair-btn" title="Remove Attribute">&times;</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn product-action-btn btn-outline-secondary btn-sm add-attribute-value-pair-btn mt-1">Add Attribute</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn product-action-btn btn-sm btn-outline-primary" id="add-variation-matrix-btn">Add Variation Combination</button>
                            </div>
                            <button type="submit" class="btn product-action-btn btn-new">Create Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn product-action-btn btn-outline-secondary">Cancel</a>
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

        // Initialize Select2 for Product Name with tagging (client-side search)
        $('.select2-tags').select2({
            theme: 'bootstrap-5',
            width: '100%',
            tags: true,
                ajax: {
                url: '{{ route("admin.master-products.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name + (item.synonyms ? ' (' + item.synonyms + ')' : ''),
                                id: item.name, // Use name as ID because controller expects name string
                                synonyms: item.synonyms
                            }
                        })
                    };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) return data.text;
                
                // If it's a new tag user is typing
                if (data.newTag) {
                    return $('<div><strong>Create New:</strong> ' + data.text + '</div>');
                }
                
                // Standard result
                var name = data.id || data.text; 
                if(data.text && data.text.indexOf('(') !== -1){
                     // Use the pre-formatted text from processResults
                     return $('<div>' + data.text + '</div>');
                }

                return $('<div>' + name + '</div>');
            },
            templateSelection: function(data) {
                 // When selected, just show the NAME part (remove synonyms logic if present in text)
                 var text = data.text || data.id;
                 var match = text.match(/^(.+?)\s*\(/);
                 if (match) {
                     return match[1];
                 }
                 return text;
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
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

    // Variation matrix dynamic loading
    function loadVariationMatrixValues(attributeId, valueSelect, selectedValues = []) {
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
                    let selected = selectedValues && selectedValues.includes(val.id) ? ' selected' : '';
                    options += `<option value="${val.id}"${selected}>${val.value}</option>`;
                });
                valueSelect.innerHTML = options;
                valueSelect.disabled = false;
                $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
            });
    }
    function bindVariationMatrixEvents() {
        document.querySelectorAll('.variation-attribute-select').forEach(function(select) {
            select.onchange = function() {
                const pair = select.closest('.variation-attribute-value-pair');
                const valueSelect = pair.querySelector('.variation-value-select');
                loadVariationMatrixValues(select.value, valueSelect, []);
            };
        });
        document.querySelectorAll('.variation-value-select').forEach(function(sel) {
            $(sel).select2({ theme: 'bootstrap-5', width: '100%' });
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        bindVariationMatrixEvents();
    });
    let variationMatrixIndex = 1;
    document.getElementById('add-variation-matrix-btn').addEventListener('click', function() {
        var list = document.getElementById('variation-matrix-list');
        var row = document.createElement('div');
        row.className = 'row g-2 mb-2 variation-matrix-row';
        row.innerHTML = `
            <div class="col-md-2">
                <input type="text" class="form-control" name="variation_matrix[${variationMatrixIndex}][sku]" placeholder="SKU (optional)">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="variation_matrix[${variationMatrixIndex}][price]" placeholder="Price" step="0.01">
            </div>
            <div class="col-md-1">
                <input type="number" class="form-control" name="variation_matrix[${variationMatrixIndex}][stock]" placeholder="Stock">
            </div>
            <div class="col-md-1 d-flex flex-column align-items-center">
                <input type="file" class="form-control mb-1 variation-image-input" name="variation_matrix[${variationMatrixIndex}][image]" accept="image/*">
                <button type="button" class="btn product-action-btn btn-outline-danger btn-sm remove-variation-matrix-btn" title="Remove Variation">&times;</button>
            </div>
            <div class="col-md-6">
                <div class="variation-attributes-group">
                    <div class="row g-1 mb-1 variation-attribute-value-pair">
                        <div class="col-md-5">
                            <select class="form-select variation-attribute-select" name="variation_matrix[${variationMatrixIndex}][attributes][0][attribute_id]" required>
                                <option value="">-- Select Attribute --</option>
                                @foreach(App\Models\VariationAttribute::all() as $attr)
                                    <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select class="form-select variation-value-select" name="variation_matrix[${variationMatrixIndex}][attributes][0][value_id][]" multiple required disabled>
                                <option value="">-- Select Value --</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn product-action-btn btn-outline-danger btn-sm remove-attribute-value-pair-btn" title="Remove Attribute">&times;</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn product-action-btn btn-outline-secondary btn-sm add-attribute-value-pair-btn mt-1">Add Attribute</button>
            </div>
        `;
        list.appendChild(row);
        bindVariationMatrixEvents();
        variationMatrixIndex++;
    });
    document.getElementById('variation-matrix-list').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variation-matrix-btn')) {
            e.target.closest('.variation-matrix-row').remove();
        }
        if (e.target.classList.contains('add-attribute-value-pair-btn')) {
            const row = e.target.closest('.variation-matrix-row');
            const group = row.querySelector('.variation-attributes-group');
            const pairCount = group.querySelectorAll('.variation-attribute-value-pair').length;
            const matrixIdx = Array.from(document.getElementById('variation-matrix-list').children).indexOf(row);
            const pair = document.createElement('div');
            pair.className = 'row g-1 mb-1 variation-attribute-value-pair';
            pair.innerHTML = `
                <div class="col-md-5">
                    <select class="form-select variation-attribute-select" name="variation_matrix[${matrixIdx}][attributes][${pairCount}][attribute_id]" required>
                        <option value="">-- Select Attribute --</option>
                        @foreach(App\Models\VariationAttribute::all() as $attr)
                            <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <select class="form-select variation-value-select" name="variation_matrix[${matrixIdx}][attributes][${pairCount}][value_id][]" multiple required disabled>
                        <option value="">-- Select Value --</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button type="button" class="btn product-action-btn btn-outline-danger btn-sm remove-attribute-value-pair-btn" title="Remove Attribute">&times;</button>
                </div>
            `;
            group.appendChild(pair);
            bindVariationMatrixEvents();
        }
        if (e.target.classList.contains('remove-attribute-value-pair-btn')) {
            e.target.closest('.variation-attribute-value-pair').remove();
        }
    });
    // Variation image preview (per row)
    document.getElementById('variation-matrix-list').addEventListener('change', function(e) {
        if (e.target.classList.contains('variation-image-input')) {
            const input = e.target;
            let preview = input.nextElementSibling;
            if (!preview || !preview.classList.contains('variation-image-preview')) {
                preview = document.createElement('img');
                preview.className = 'variation-image-preview rounded border mt-1';
                preview.style.height = '40px';
                preview.style.width = '40px';
                preview.style.objectFit = 'cover';
                input.parentNode.insertBefore(preview, input.nextSibling);
            }
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.title = input.files[0].name;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.title = '';
            }
        }
    });
</script>




