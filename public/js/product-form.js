/**
 * Product Management Form Interactivity
 * Handles: Tabs, Image Uploads, Batch Variations, Category Tree
 */

const ProductForm = {
    variationIndex: 0,
    attributeList: [],

    init(config) {
        this.variationIndex = config.initialVariationIndex || 0;
        this.attributeList = config.attributes || [];
        this.bindEvents();
        this.initImageUpload();
        this.initCategoryTree(config.categoryData);
    },

    bindEvents() {
        // Add manual variation
        document.getElementById('add-variation-btn')?.addEventListener('click', () => this.addVariationRow());

        // Add attribute to variation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-attr-btn')) {
                this.addAttributeValuePair(e.target.closest('.variation-row'));
            }
            if (e.target.classList.contains('remove-variation-btn')) {
                e.target.closest('.variation-row').remove();
            }
            if (e.target.classList.contains('remove-attr-btn')) {
                e.target.closest('.attr-pair-row').remove();
            }
        });

        // Batch Variation Generator
        document.getElementById('open-generator-btn')?.addEventListener('click', () => this.toggleGenerator());
        document.getElementById('run-generator-btn')?.addEventListener('click', () => this.generateBatchVariations());
    },

    initImageUpload() {
        const zone = document.getElementById('image-upload-zone');
        const input = document.getElementById('images-main');
        const preview = document.getElementById('main-previews');
        let filesList = [];

        if (!zone) return;

        zone.addEventListener('click', () => input.click());

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, () => zone.classList.remove('dragover'));
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            this.handleFiles(e.dataTransfer.files, input, preview);
        });

        input.addEventListener('change', () => {
            this.handleFiles(input.files, input, preview);
        });
    },

    handleFiles(files, input, previewContainer) {
        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const item = document.createElement('div');
                item.className = 'pm-preview-item';
                item.innerHTML = `
                    <img src="${e.target.result}">
                    <div class="pm-preview-remove">&times;</div>
                `;
                item.querySelector('.pm-preview-remove').onclick = () => item.remove();
                previewContainer.appendChild(item);
                
                // Automate variation creation
                this.addVariationRow({ 
                    file: file,
                    image_url: e.target.result 
                });
            };
            reader.readAsDataURL(file);
        });
    },

    initCategoryTree(data) {
        if (!document.getElementById('category_tree')) return;

        $('#category_tree').jstree({
            core: { data: data, multiple: false, check_callback: true },
            plugins: ['wholerow', 'types', 'search'],
            types: {
                folder: { icon: 'cil-folder text-warning' },
                tag: { icon: 'cil-tag text-info' }
            }
        });

        $('#category-search').on('keyup', function () {
            const v = $(this).val();
            $('#category_tree').jstree(true).search(v);
        });

        $('#category_tree').on('changed.jstree', (e, data) => {
            const selected = data.selected[0];
            const node = data.instance.get_node(selected);
            if (node && node.children.length === 0) {
                document.getElementById('category_id').value = selected;
                this.updateBreadcrumb(data.instance, node);
            }
        });
    },

    updateBreadcrumb(tree, node) {
        let path = [];
        let curr = node;
        while (curr && curr.id !== '#' && curr.id !== 'all-categories') {
            path.unshift(curr.text);
            curr = tree.get_node(curr.parent);
        }
        document.getElementById('category_breadcrumb').innerText = path.join(' > ');
    },

    addVariationRow(data = {}) {
        const container = document.getElementById('variation-container');
        const row = document.createElement('div');
        row.className = 'variation-row';
        row.dataset.index = this.variationIndex;
        row.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="pm-label">SKU</label>
                    <input type="text" name="variations[${this.variationIndex}][sku]" value="${data.sku || ''}" class="pm-input">
                </div>
                <div class="col-md-2">
                    <label class="pm-label">Price</label>
                    <input type="number" step="0.01" name="variations[${this.variationIndex}][price]" value="${data.price || ''}" class="pm-input">
                </div>
                <div class="col-md-1">
                    <label class="pm-label">Stock</label>
                    <input type="number" name="variations[${this.variationIndex}][stock]" value="${data.stock || ''}" class="pm-input">
                </div>
                <div class="col-md-2 text-center">
                    <label class="pm-label">Image</label>
                    <div class="variation-img-preview mb-2" style="height: 60px; width: 60px; margin: 0 auto; background: #eee; border-radius: 4px; overflow: hidden;">
                         ${data.image_url ? `<img src="${data.image_url}" style="width:100%; height:100%; object-fit:cover;">` : '<i class="cil-image" style="line-height: 60px; color: #ccc;"></i>'}
                    </div>
                    <input type="file" name="variations[${this.variationIndex}][image]" class="d-none var-img-input" accept="image/*">
                    <button type="button" class="btn btn-sm btn-outline-secondary var-img-btn">Select</button>
                    ${data.id ? `<input type="hidden" name="variations[${this.variationIndex}][id]" value="${data.id}">` : ''}
                </div>
                <div class="col-md-4">
                    <label class="pm-label">Attributes</label>
                    <div class="attr-container"></div>
                    <button type="button" class="pm-btn pm-btn-secondary btn-sm mt-2 add-attr-btn">+ Attribute</button>
                </div>
            </div>
            <button type="button" class="pm-btn pm-btn-secondary btn-sm remove-variation-btn text-danger">Remove</button>
        `;
        container.appendChild(row);

        const imgInput = row.querySelector('.var-img-input');
        const imgBtn = row.querySelector('.var-img-btn');
        const imgPreview = row.querySelector('.variation-img-preview');

        imgBtn.onclick = () => imgInput.click();
        
        // Handle programmatic file assignment
        if (data.file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(data.file);
            imgInput.files = dataTransfer.files;
        }

        imgInput.onchange = () => {
            const file = imgInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imgPreview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                };
                reader.readAsDataURL(file);
            }
        };

        if (data.attributes) {
            data.attributes.forEach(attr => this.addAttributeValuePair(row, attr));
        } else {
            this.addAttributeValuePair(row);
        }

        this.variationIndex++;
    },

    addAttributeValuePair(container, data = {}) {
        const variationIndex = container.dataset.index;
        const attrContainer = container.querySelector('.attr-container');
        const idx = attrContainer.querySelectorAll('.attr-pair-row').length;
        const div = document.createElement('div');
        div.className = 'attr-pair-row row g-2 mb-1';

        let options = '<option value="">- Select -</option>';
        this.attributeList.forEach(attr => {
            options += `<option value="${attr.id}" ${data.id == attr.id ? 'selected' : ''}>${attr.name}</option>`;
        });

        div.innerHTML = `
            <div class="col-6">
                <select name="variations[${variationIndex}][attributes][${idx}][attribute_id]" class="pm-select attr-select">
                    ${options}
                </select>
            </div>
            <div class="col-5">
                <select name="variations[${variationIndex}][attributes][${idx}][value_id][]" class="pm-select value-select" multiple disabled>
                    <option value="">- Value -</option>
                </select>
            </div>
            <div class="col-1">
                <button type="button" class="remove-attr-btn text-danger border-0 bg-transparent">&times;</button>
            </div>
        `;
        attrContainer.appendChild(div);

        const attrSelect = div.querySelector('.attr-select');
        const valueSelect = div.querySelector('.value-select');

        attrSelect.onchange = () => this.loadAttributeValues(attrSelect.value, valueSelect, data.value_ids);
        if (data.id) this.loadAttributeValues(data.id, valueSelect, data.value_ids);

        $(valueSelect).select2({ theme: 'bootstrap-5', width: '100%' });
    },

    loadAttributeValues(attrId, select, selected = []) {
        const attr = this.attributeList.find(a => a.id == attrId);
        if (!attr) return;

        let html = '';
        attr.values.forEach(v => {
            const isSelected = selected.includes(v.id) ? 'selected' : '';
            html += `<option value="${v.id}" ${isSelected}>${v.value}</option>`;
        });
        select.innerHTML = html;
        select.disabled = false;
        $(select).trigger('change');
    },

    toggleGenerator() {
        $('#generator-modal').collapse('toggle');
    },

    generateBatchVariations() {
        // Logic for Cartesian product generation from selected attrs
        const selections = [];
        document.querySelectorAll('.gen-attr-row').forEach(row => {
            const attrId = row.querySelector('.gen-attr-select').value;
            const values = $(row.querySelector('.gen-value-select')).val();
            if (attrId && values.length) {
                selections.push({ id: attrId, name: row.querySelector('.gen-attr-select').options[row.querySelector('.gen-attr-select').selectedIndex].text, values });
            }
        });

        if (selections.length === 0) return;

        const cartesian = (...args) => args.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())));
        const combinations = selections.length > 1 ? cartesian(...selections.map(s => s.values.map(v => ({ attrId: s.id, valueId: v })))) : selections[0].values.map(v => [{ attrId: selections[0].id, valueId: v }]);

        combinations.forEach(combo => {
            const attrs = Array.isArray(combo) ? combo : [combo];
            this.addVariationRow({
                price: document.getElementById('gen-price').value,
                stock: document.getElementById('gen-stock').value,
                attributes: attrs.map(a => ({ id: a.attrId, value_ids: [a.valueId] }))
            });
        });

        this.toggleGenerator();
    }
};

window.ProductForm = ProductForm;
