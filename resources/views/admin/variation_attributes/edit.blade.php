<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Variation Attribute') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">Edit Variation Attribute</div>
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
                        <form method="POST" action="{{ route('admin.variation-attributes.update', $variationAttribute) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label">Attribute Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $variationAttribute->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attribute Values</label>
                                <div id="values-list">
                                    @foreach($variationAttribute->values as $val)
                                        <div class="input-group mb-2 value-row">
                                            <input type="text" class="form-control" name="values[{{ $val->id }}]" value="{{ old('values.'.$val->id, $val->value) }}" required>
                                            <button type="button" class="btn action-btn delete-btn remove-value-btn" data-id="{{ $val->id }}">Remove</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm action-btn btn-new" id="add-value-btn">Add New Value</button>
                            </div>
                            <button type="submit" class="btn action-btn btn-save">Update Attribute</button>
                            <a href="{{ route('admin.variation-attributes.index') }}" class="btn action-btn btn-cancel">Cancel</a>
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
                    div.innerHTML = '<input type="text" class="form-control" name="values[new][]" placeholder="Enter value" required>' +
                        '<button type="button" class="btn btn-outline-danger remove-value-btn">Remove</button>';
                    valuesList.appendChild(div);
                });
            }
            valuesList.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-value-btn')) {
                    var row = e.target.closest('.value-row');
                    if (row) row.remove();
                }
            });
        });
    </script>
   
</x-app-layout>
