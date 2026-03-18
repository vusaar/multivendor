<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Category') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit Category</h4>
                </div>
                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf
                    @method('PUT')
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

                        <div class="mb-4">
                            <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name', $category->name) }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Description</label>
                            <textarea name="description" id="description" class="form-control border-0 bg-light rounded-3" rows="3">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label for="parent_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted mb-0">Parent Category</label>
                                <span id="category_breadcrumb" class="badge bg-soft-primary text-primary fw-600"></span>
                            </div>
                            <div class="glass-panel p-3 rounded-3" style="background: rgba(var(--midnight-rgb), 0.02)">
                                <div id="category_jstree" style="max-height:250px; overflow:auto;"></div>
                            </div>
                            <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id', $category->parent_id) }}">
                        </div>

                        @php
                            $categoryData = [
                                [
                                    'id' => 'null',
                                    'parent' => '#',
                                    'text' => 'None (Root Category)',
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
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                            <i class="cil-check"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/themes/default/style.min.css" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/jstree.min.js"></script>
<script id="category-data" type="application/json">
    {!! json_encode($categoryData) !!}
</script>
<script>
    function buildBreadcrumb(tree, node) {
        if (!node) return '';
        var names = [];
        var current = node;
        while (current && current.id !== '#' && current.id !== 'none' && current.id !== 'null') {
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
        $('#category_breadcrumb').text(breadcrumb || 'Root');
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

<style>
    .bg-soft-primary {
        background-color: rgba(225, 29, 72, 0.1) !important;
        color: var(--primary) !important;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
        border-radius: 6px;
    }
</style>
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


