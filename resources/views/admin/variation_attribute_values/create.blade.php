<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Variation Attribute Value') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">Add Variation Attribute Value</div>
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
                        <form method="POST" action="{{ route('admin.variation-attribute-values.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="variation_attribute_id" class="form-label">Attribute</label>
                                <select class="form-select" id="variation_attribute_id" name="variation_attribute_id" required>
                                    <option value="">-- Select Attribute --</option>
                                    @foreach($attributes as $attribute)
                                        <option value="{{ $attribute->id }}" {{ old('variation_attribute_id') == $attribute->id ? 'selected' : '' }}>{{ $attribute->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="value" class="form-label">Value</label>
                                <input type="text" class="form-control" id="value" name="value" value="{{ old('value') }}" required>
                            </div>
                            <button type="submit" class="btn action-btn btn-save">Add Value</button>
                            <a href="{{ route('admin.variation-attribute-values.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
