<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Category') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Create Category</h4>
                </div>
                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
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
                            <input type="text" name="name" id="name" class="form-control form-control-lg border-0 bg-light rounded-3" value="{{ old('name') }}" required placeholder="e.g. Men's Fashion">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Description</label>
                            <textarea name="description" id="description" class="form-control border-0 bg-light rounded-3" rows="3" placeholder="Describe this category...">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Parent Category</label>
                            <div class="glass-panel p-3 rounded-3" style="background: rgba(var(--midnight-rgb), 0.02)">
                                <div id="category_jstree" style="max-height:250px; overflow:auto;"></div>
                            </div>
                            <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">
                            <div class="form-text small mt-2">Select "None" if this is a top-level category.</div>
                        </div>

                        @php
                            $categoryData = [
                                [
                                    'id' => 'none',
                                    'parent' => '#',
                                    'text' => 'None (Root Category)',
                                    'icon' => '<svg class="cil-ban text-secondary" width="1em" height="1em"><use xlink:href="/icons/coreui.svg#cil-ban"/></svg>',
                                    'state' => [
                                        'opened' => true,
                                        'selected' => old('parent_id') ? false : true,
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
                                        'selected' => old('parent_id') == $cat->id,
                                    ],
                                ];
                            }
                        @endphp
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                            <i class="cil-check"></i> Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Load CoreUI SVG sprite for icons -->
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


