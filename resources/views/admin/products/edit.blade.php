<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Product') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">Edit Product</div>
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
                        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                                     <div class="mb-6">
                                         <div class="row g-3 mb-3">
                                             <div class="col-md-12 col-xs-12">
                                                 <label for="vendor_id" class="form-label">Vendor</label>
                                                 <select class="form-select" id="vendor_id" name="vendor_id">
                                                      <option value="">-- Select Vendor --</option>
                                                      @foreach($vendors as $vendor)
                                                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                                      @endforeach
                                                 </select>
                                             </div>
                                         </div>
                                         <div class="row g-3 mb-3">
                                             <div class="col-md-7 col-xs-12">
                                                <label for="name" class="form-label">Item / Product Name</label>
                                                <select class="form-select select2-tags" id="name" name="name" required>
                                                    @if(old('name', $product->name))
                                                        <option value="{{ old('name', $product->name) }}" selected>
                                                            {{ old('name', $product->name) }}
                                                            @if($product->masterProduct && $product->masterProduct->synonyms)
                                                                ({{ $product->masterProduct->synonyms }})
                                                            @endif
                                                        </option>
                                                    @endif
                                                </select>
                                             </div>
                                             <div class="col-md-5 col-xs-12">
                                                 <label for="brand_id" class="form-label">Brand</label>
                                                 <select class="form-select" id="brand_id" name="brand_id" required>
                                                      <option value="">-- Select Brand -- </option>
                                                      @foreach($brands as $brand)
                                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                      @endforeach
                                                 </select>
                                             </div>
                                         </div>
                                     </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6 col-xs-12">
                                    <label for="category_tree" class="form-label">Category</label>
                                    <div id="category_breadcrumb" class="mb-2 text-secondary small"></div>
                                    <div id="category_tree" style="max-height:200px;overflow:auto; border:1px solid #cfd4de; border-radius:6px; padding:5px;"></div>
                                    <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $product->category_id) }}">
                                </div>
                                <div class="col-md-6 col-xs-12 ">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control mt-3" id="description" name="description" style="min-height:200px;overflow:auto; border:1px solid #cfd4de; border-radius:6px; padding:5px;">{{ old('description', $product->description) }}</textarea>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6 col-xs-12">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-3 col-xs-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-9 col-xs-8">
                                    <label for="images" class="form-label">Product Images</label>
                                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                    <div id="existing-images-list" class="mb-2 d-flex flex-wrap gap-2">
                                        @foreach($product->images as $image)
                                            <div class="position-relative product-image-preview">
                                                <img src="{{ asset('storage/' . $image->image) }}" class="rounded border" style="height:64px;width:64px;object-fit:cover;" title="{{ basename($image->image) }}">
                                                <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill pointer remove-existing-image-btn" style="background:rgba(128,128,128,0.6);cursor:pointer;border:none;z-index:2;" title="Remove">&times;</span>
                                                <input type="hidden" name="existing_images[]" value="{{ $image->id }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <div id="image-preview-list" class="mt-2 d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Product Variations</label>
                                <div id="variation-list">
                                    @php $variationIndex = 0; @endphp
                                    @foreach($product->variations as $variation)
                                        <div class="row g-2 mb-2 variation-matrix-row">
                                            <input type="hidden" name="variations[{{ $variationIndex }}][id]" value="{{ $variation->id }}">
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" name="variations[{{ $variationIndex }}][sku]" value="{{ $variation->sku }}" placeholder="SKU (optional)">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control" name="variations[{{ $variationIndex }}][price]" value="{{ $variation->price }}" placeholder="Price" step="0.01">
                                            </div>
                                            <div class="col-md-1">
                                                <input type="number" class="form-control" name="variations[{{ $variationIndex }}][stock]" value="{{ $variation->stock }}" placeholder="Stock">
                                            </div>
                                            <div class="col-md-1 d-flex flex-column align-items-center">
                                                <input type="file" class="form-control mb-1 variation-image-input" name="variations[{{ $variationIndex }}][image]" accept="image/*">
                                                @php
                                                    $variationImage = $variation->variationImages->first() ?? null;
                                               
                                                 $variation_image_path = $variationImage ? $variationImage->image_path : '';

                                                 $variation_image_alt_text = $variationImage ? $variationImage->alt_text : '';

                                                @endphp

                                                <img src="{{ asset('storage/'. $variation_image_path) }}" class="variation-image-preview rounded border mt-1" style="height:40px;width:40px;object-fit:cover;" alt="{{ $variation_image_alt_text }}">

                                                <button type="button" class="btn btn-outline-danger btn-sm remove-variation-matrix-btn product-action-btn" title="Remove Variation">&times;</button>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="variation-attributes-group">
                                                    @php $pairCount = 0; @endphp
                                                    @foreach($variation->attributeValues->groupBy('variation_attribute_id') as $attributeId => $values)
                                                        <div class="row g-1 mb-1 variation-attribute-value-pair">
                                                            <div class="col-md-5">
                                                                <select class="form-select variation-attribute-select" name="variations[{{ $variationIndex }}][attributes][{{ $pairCount }}][attribute_id]" required>
                                                                    <option value="">-- Select Attribute --</option>
                                                                    @foreach($variationAttributes as $attr)
                                                                        <option value="{{ $attr->id }}" {{ $attr->id == $attributeId ? 'selected' : '' }}>{{ $attr->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <select class="form-select variation-value-select" name="variations[{{ $variationIndex }}][attributes][{{ $pairCount }}][value_id][]" multiple required>
                                                                    <option value="">-- Select Value --</option>
                                                                    @php
                                                                        // Find the attribute model from the collection we passed
                                                                        $currentAttribute = $variationAttributes->firstWhere('id', $attributeId);
                                                                        $currentValues = $currentAttribute ? $currentAttribute->values : collect([]);
                                                                    @endphp
                                                                    @foreach($currentValues as $val)
                                                                        <option value="{{ $val->id }}" {{ in_array($val->id, $values->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $val->value }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 d-flex align-items-center">
                                                                <button type="button" class="btn btn-outline-danger btn-sm remove-attribute-value-pair-btn product-action-btn" title="Remove Attribute">&times;</button>
                                                            </div>
                                                        </div>
                                                        @php $pairCount++; @endphp
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-sm add-attribute-value-pair-btn mt-1 product-action-btn btn-new">Add Attribute</button>
                                            </div>
                                        </div>
                                        @php $variationIndex++; @endphp
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm product-action-btn btn-new mb-3" id="add-variation-matrix-btn">Add Variation Combination</button>
                            </div>
                            <button type="submit" class="btn btn-sm product-action-btn btn-save mb-3">Update Product</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm product-action-btn btn-cancel mb-3">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    
    <script>
    // Pass attribute list to JS
    var variationAttributes = @json($variationAttributes);

    // Add attribute-value pair to a variation row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-attribute-value-pair-btn')) {
            var row = e.target.closest('.variation-matrix-row');
            var group = row.querySelector('.variation-attributes-group');
            var pairCount = group.querySelectorAll('.variation-attribute-value-pair').length;
            // Build the HTML for a new attribute-value pair
            var attrSelectHtml = `<select class="form-select variation-attribute-select" name="${getVariationPrefix(row)}[attributes][${pairCount}][attribute_id]" required><option value="">-- Select Attribute --</option>`;
            variationAttributes.forEach(function(attr) {
                attrSelectHtml += `<option value="${attr.id}">${attr.name}</option>`;
            });
            attrSelectHtml += `</select>`;
            var valueSelectHtml = `<select class="form-select variation-value-select" name="${getVariationPrefix(row)}[attributes][${pairCount}][value_id][]" multiple required><option value="">-- Select Value --</option></select>`;
            var html = `<div class="row g-1 mb-1 variation-attribute-value-pair">
                <div class="col-md-5">${attrSelectHtml}</div>
                <div class="col-md-5">${valueSelectHtml}</div>
                <div class="col-md-2 d-flex align-items-center">
                    <button type="button" class="btn btn-outline-danger product-action-btn btn-sm remove-attribute-value-pair-btn" title="Remove Attribute">&times;</button>
                </div>
            </div>`;
            group.insertAdjacentHTML('beforeend', html);
            // Bind change event and load values for the new select
            var newAttrSelect = group.querySelectorAll('.variation-attribute-select')[pairCount];
            var newValueSelect = group.querySelectorAll('.variation-value-select')[pairCount];
            $(newValueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
            newAttrSelect.addEventListener('change', function() {
                loadVariationValues(newAttrSelect.value, newValueSelect, []);
            });
        }
        // Remove attribute-value pair
        if (e.target.classList.contains('remove-attribute-value-pair-btn')) {
            var pair = e.target.closest('.variation-attribute-value-pair');
            if (pair) pair.remove();
        }
    });

    // Helper to get the correct prefix for variation inputs
    function getVariationPrefix(row) {
        var idInput = row.querySelector('input[type="hidden"][name^="variations["]');
        if (idInput) {
            var match = idInput.name.match(/variations\[(\d+)\]/);
            if (match) return `variations[${match[1]}]`;
        }
        // fallback: find first input with name variations[xx]
        var input = row.querySelector('input[name^="variations["]');
        if (input) {
            var match = input.name.match(/variations\[(\d+)\]/);
            if (match) return `variations[${match[1]}]`;
        }
        return 'variations[0]';
    }
        // Prepare category data for jsTree (up to 3 levels, with CoreUI icons)
        var categoryData = [];
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
                state: { @if(old('category_id', $product->category_id) == $parent->id) selected: true @endif }
            });
            @foreach($parent->children as $child)
                categoryData.push({
                    id: '{{ $child->id }}',
                    parent: '{{ $parent->id }}',
                    text: @json($child->name),
                    type: '{{ $child->children->isEmpty() ? 'tag' : 'folder' }}',
                    state: { @if(old('category_id', $product->category_id) == $child->id) selected: true @endif }
                });
                @foreach($child->children as $grandchild)
                    categoryData.push({
                        id: '{{ $grandchild->id }}',
                        parent: '{{ $child->id }}',
                        text: @json($grandchild->name),
                        type: 'tag',
                        state: { @if(old('category_id', $product->category_id) == $grandchild->id) selected: true @endif }
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
                        icon: 'cil-folder text-warning',
                    },
                    tag: {
                        icon: 'cil-tag text-info',
                    }
                }
            });
            $('#category_tree').on('before.jstree', function(e, data) {
                if (data.func === 'select_node') {
                    var node = $('#category_tree').jstree(true).get_node(data.args[0]);
                    if (node.children.length > 0) {
                        e.preventDefault();
                    }
                }
            });
            $('#category_tree').on('ready.jstree', function() {
                var selected = $('#category_id').val();
                if (selected) {
                    var node = $('#category_tree').jstree(true).get_node(selected);
                    if (node && node.children.length === 0) {
                        $('#category_tree').jstree('select_node', selected);
                    }
                }
                updateBreadcrumb();
            });
            $('#category_tree').on('changed.jstree', function(e, data) {
                var selected = data.selected[0] || '';
                var tree = $('#category_tree').jstree(true);
                var node = tree.get_node(selected);
                if (node && node.children.length === 0) {
                    $('#category_id').val(selected);
                    var parents = node.parents;
                    parents.forEach(function(parentId) {
                        if (parentId !== '#') {
                            tree.open_node(parentId);
                        }
                    });
                } else {
                    $('#category_id').val('');
                }
                updateBreadcrumb();
            });
            $('#category_tree').on('select_node.jstree', function(e, data) {
                var tree = $('#category_tree').jstree(true);
                var node = data.node;
                if (node.children.length > 0) {
                    if (tree.is_open(node)) {
                        tree.close_node(node);
                    } else {
                        tree.open_node(node);
                    }
                    tree.deselect_node(node);
                }
            });
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
            function updateBreadcrumb() {
                var tree = $('#category_tree').jstree(true);
                var selected = $('#category_id').val();
                var node = tree.get_node(selected);
                var breadcrumb = buildBreadcrumb(tree, node);
                $('#category_breadcrumb').text(breadcrumb);
            }

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
                                    id: item.name, 
                                    synonyms: item.synonyms
                                }
                            })
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    
                    if (data.newTag) {
                        return $('<div><strong>Create New:</strong> ' + data.text + '</div>');
                    }
                    
                    var name = data.id || data.text; 
                    if(data.text && data.text.indexOf('(') !== -1){
                        return $('<div>' + data.text + '</div>');
                    }

                    return $('<div>' + name + '</div>');
                },
                templateSelection: function(data) {
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
        // Image preview for product images with delete icon (new uploads)
        document.getElementById('images').addEventListener('change', function(event) {
            var preview = document.getElementById('image-preview-list');
            // Only clear previews for new uploads, keep existing images
            var existing = Array.from(preview.querySelectorAll('form, img')).filter(function(el) {
                return el.tagName === 'FORM' || el.tagName === 'IMG';
            });
            preview.innerHTML = '';
            existing.forEach(function(el) { preview.appendChild(el); });
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
                        var del = document.createElement('span');
                        del.className = 'position-absolute top-0 end-0 translate-middle badge rounded-pill pointer';
                        del.style.cursor = 'pointer';
                        del.style.zIndex = '2';
                        del.style.background = 'rgba(128,128,128,0.6)';
                        del.innerHTML = '&times;';
                        del.title = 'Remove';
                        del.onclick = function() {
                            var dt = new DataTransfer();
                            var input = document.getElementById('images');
                            Array.from(input.files).forEach(function(f, i) {
                                if (i !== idx) dt.items.add(f);
                            });
                            input.files = dt.files;
                            wrapper.remove();
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
        
        function loadVariationValues(attributeId, valueSelect, selectedValues = []) {
            if (!attributeId) {
                valueSelect.innerHTML = '<option value="">-- Select Value --</option>';
                valueSelect.disabled = true;
                $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
                return;
            }
            
            // Use client-side data instead of fetch
            var attribute = variationAttributes.find(a => a.id == attributeId);
            var values = attribute ? attribute.values : [];
            
            let options = '<option value="">-- Select Value --</option>';
            values.forEach(function(val) {
                let selected = selectedValues && selectedValues.includes(val.value) ? ' selected' : '';
                options += `<option value="${val.id}"${selected}>${val.value}</option>`;
            });
            valueSelect.innerHTML = options;
            valueSelect.disabled = false;
            $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
        }

        function bindVariationAttributeEvents() {
            document.querySelectorAll('.variation-attribute-select').forEach(function(select) {
                // Remove old listener to avoid duplicates if re-binding
                select.onchange = null;
                select.onchange = function() {
                    const row = select.closest('.variation-matrix-row');
                    const valueSelect = row.querySelector('.variation-value-select');
                    loadVariationValues(select.value, valueSelect, []);
                };
            });
            document.querySelectorAll('.variation-value-select').forEach(function(sel) {
                $(sel).select2({ theme: 'bootstrap-5', width: '100%' });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            bindVariationAttributeEvents();
        });
        let variationIndex = {{ isset($variationIndex) ? $variationIndex : 0 }};
        document.getElementById('add-variation-matrix-btn').addEventListener('click', function() {
            var list = document.getElementById('variation-list');
            var row = document.createElement('div');
            row.className = 'row g-2 mb-2 variation-matrix-row';
            row.innerHTML = `
                <div class="col-md-2">
                    <input type="text" class="form-control" name="variations[${variationIndex}][sku]" placeholder="SKU (optional)">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="variations[${variationIndex}][price]" placeholder="Price" step="0.01">
                </div>
                <div class="col-md-1">
                    <input type="number" class="form-control" name="variations[${variationIndex}][stock]" placeholder="Stock">
                </div>
                <div class="col-md-1 d-flex flex-column align-items-center">
                    <input type="file" class="form-control mb-1 variation-image-input" name="variations[${variationIndex}][image]" accept="image/*">
                    <img src="" class="variation-image-preview rounded border mt-1" style="height:40px;width:40px;object-fit:cover;display:none;" alt="">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-variation-matrix-btn product-action-btn" title="Remove Variation">&times;</button>
                </div>
                <div class="col-md-6">
                    <div class="variation-attributes-group">
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm add-attribute-value-pair-btn mt-1 product-action-btn">Add Attribute</button>
                </div>
            `;
            list.appendChild(row);
            bindVariationAttributeEvents();
            variationIndex++;
        });
        document.getElementById('variation-list').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-variation-matrix-btn')) {
                e.target.closest('.variation-matrix-row').remove();
            }
        });
        // Remove existing product image preview client-side only
        $(document).on('click', '.remove-existing-image-btn', function() {
            var wrapper = $(this).closest('.product-image-preview');
            // Remove the hidden input for this image
            wrapper.find('input[type="hidden"][name="existing_images[]"]').remove();
            wrapper.remove();
        });
        // Fix: When new images are selected, do NOT clear out existing image previews or their hidden inputs
        const imageInput = document.getElementById('images');
        const previewList = document.getElementById('image-preview-list');
        if (imageInput) {
            imageInput.addEventListener('change', function(event) {
                // Clear previous new image previews only (not existing images)
                //previewList.innerHTML = '';
                var files = event.target.files;
                console.log(files.length);
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
                            var del = document.createElement('span');
                            del.className = 'position-absolute top-0 end-0 translate-middle badge rounded-pill pointer';
                            del.style.cursor = 'pointer';
                            del.style.zIndex = '2';
                            del.style.background = 'rgba(128,128,128,0.6)';
                            del.innerHTML = '&times;';
                            del.title = 'Remove';
                            del.onclick = function() {
                                var dt = new DataTransfer();
                                var input = document.getElementById('images');
                                Array.from(input.files).forEach(function(f, i) {
                                    if (i !== idx) dt.items.add(f);
                                });
                                input.files = dt.files;
                                wrapper.remove();
                            };
                            wrapper.appendChild(img);
                            wrapper.appendChild(del);
                            previewList.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });
        }
    </script>
    <script>
    // Live preview for variation image selection
    function bindVariationImagePreviewEvents() {
        document.querySelectorAll('.variation-image-input').forEach(function(input) {
            input.addEventListener('change', function(event) {
                console.log('image change event fired');
                var file = input.files[0];
                if (file && file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // Find the preview image in the same variation row
                        var preview = input.closest('.variation-matrix-row').querySelector('.variation-image-preview');
                        if (preview) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        bindVariationImagePreviewEvents();
    });
    // If variations are dynamically added, re-bind events
    document.getElementById('add-variation-matrix-btn').addEventListener('click', function() {
        setTimeout(bindVariationImagePreviewEvents, 100);
    });
    </script>
    
   
    

</x-app-layout>
