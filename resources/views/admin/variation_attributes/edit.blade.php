<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Variation Attribute') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="glass-card mb-4">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit Variation Attribute</h4>
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
                        <form method="POST" action="{{ route('admin.variation-attributes.update', $variationAttribute) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Attribute Name</label>
                                <input type="text" class="form-control form-control-lg border-0 bg-light rounded-3" id="name" name="name" value="{{ old('name', $variationAttribute->name) }}" required placeholder="e.g. Color">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-600 small text-uppercase tracking-wider text-muted mb-3">Manage Values</label>
                                <div id="values-list">
                                    @foreach($variationAttribute->values as $val)
                                        <div class="input-group mb-2 value-row">
                                            <input type="text" class="form-control border-0 bg-light rounded-start-3" name="values[{{ $val->id }}]" value="{{ old('values.'.$val->id, $val->value) }}" required>
                                            <button type="button" class="btn btn-outline-danger px-3 remove-value-btn" data-id="{{ $val->id }}">
                                                <i class="cil-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-link text-primary p-0 fw-600 small mt-2" id="add-value-btn" style="text-decoration: none;">
                                    <i class="cil-plus"></i> Add New Value
                                </button>
                            </div>

                            <div class="d-flex justify-content-end gap-3 pt-3">
                                <a href="{{ route('admin.variation-attributes.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                                    Update Attribute
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addValueBtn = document.getElementById('add-value-btn');
            var valuesList = document.getElementById('values-list');
            if (addValueBtn) {
                addValueBtn.addEventListener('click', function() {
                    var div = document.createElement('div');
                    div.className = 'input-group mb-2 value-row';
                    div.innerHTML = '<input type="text" class="form-control border-0 bg-light rounded-start-3" name="values[new][]" placeholder="Enter value" required>' +
                        '<button type="button" class="btn btn-outline-danger px-3 remove-value-btn"><i class="cil-trash"></i></button>';
                    valuesList.appendChild(div);
                });
            }
            valuesList.addEventListener('click', function(e) {
                var btn = e.target.closest('.remove-value-btn');
                if (btn) {
                    var row = btn.closest('.value-row');
                    if (row) row.remove();
                }
            });
        });
    </script>
   
</x-app-layout>
