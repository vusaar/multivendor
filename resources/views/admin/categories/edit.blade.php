<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Category') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-0">
        <div class="card-header">
            <h5 class="mb-0">Edit Category</h5>
        </div>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-6 col-xs-12">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <span id="category_breadcrumb" class="ms-2 text-secondary small"></span>
<script>
    function buildBreadcrumb(tree, node) {
        if (!node) return '';
        var names = [];
        var current = node;
        while (current && current.id !== '#' && current.id !== 'none') {
            names.unshift(current.text);
            if (!current.parents || !current.parents.length) break;
            current = tree.get_node(current.parent);
        }
        return names.join(' / ');
    }
    function updateBreadcrumb() {
        var tree = $('#category_jstree').jstree(true);
        var selected = $('#parent_id').val();
        var node = tree.get_node(selected);
        var breadcrumb = buildBreadcrumb(tree, node);
        $('#category_breadcrumb').text(breadcrumb);
    }
    document.addEventListener('DOMContentLoaded', function() {
        var categoryData = JSON.parse(document.getElementById('category-data').textContent);
        $('#category_jstree').jstree({
            core: {
                data: categoryData,
                multiple: false,
                themes: { }
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
        $('#category_jstree').on('changed.jstree', function (e, data) {
            var selected = data.selected[0] || '';
            $('#parent_id').val(selected);
            updateBreadcrumb();
        });
        $('#category_jstree').on('ready.jstree', function() {
            updateBreadcrumb();
        });
    });
</script>
                            <div id="category_jstree" style="max-height:200px;overflow:auto; border:1px solid #cfd4de; border-radius:6px; padding:5px;"></div>
                            <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id', $category->parent_id) }}">
                        </div>
                    </div>
@php
    $categoryData = [
        [
            'id' => 'null',
            'parent' => '#',
            'text' => 'None',
            'icon' => '<svg class="cil-ban text-secondary" width="1em" height="1em"><use xlink:href="/icons/coreui.svg#cil-ban"/></svg>',
            'state' => [
                'opened' => true,
                'selected' => old('parent_id', $category->parent_id) ? false : true,
            ],
        ]
    ];
    $categoryIds = $categories->pluck('id')->toArray();
    $parentIds = $categories->pluck('parent_id')->filter()->unique()->toArray();
    foreach ($categories as $cat) {
        $parent = $cat->parent_id ? (string)$cat->parent_id : '#';
        $id = (string)$cat->id === '#' ? 'cat_' . $cat->id : (string)$cat->id;
        $isParent = in_array($cat->id, $parentIds);
        $icon = $isParent
            ? '<svg class="cil-folder text-warning" width="1em" height="1em"><use xlink:href="/icons/coreui.svg#cil-folder"/></svg>'
            : '<svg class="cil-tag text-info" width="1em" height="1em"><use xlink:href="/icons/coreui.svg#cil-tag"/></svg>';
        $categoryData[] = [
            'id' => $id,
            'parent' => $parent,
            'text' => $cat->name,
            'icon' => $icon,
            'state' => [
                'selected' => old('parent_id', $category->parent_id) == $cat->id,
            ],
        ];
    }
@endphp
            </div>
            <div class="card-footer text-start">
                
                <button type="submit" class="btn action-btn btn-save"> Update</button>

                <a href="{{ route('admin.categories.index') }}" class="btn action-btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>


<link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/themes/default/style.min.css" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/jstree.min.js"></script>
<script id="category-data" type="application/json">
    {!! json_encode($categoryData) !!}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var categoryData = JSON.parse(document.getElementById('category-data').textContent);
        $('#category_jstree').jstree({
            core: {
                data: categoryData,
                multiple: false,
                themes: { }
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
        $('#category_jstree').on('changed.jstree', function (e, data) {
            var selected = data.selected[0] || '';
            $('#parent_id').val(selected);
        });
    });
</script>


