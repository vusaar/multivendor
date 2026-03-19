<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Variation Attribute Value') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card mb-4">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Edit Attribute Value</h4>
                </div>
                <form method="POST" action="{{ route('admin.variation-attribute-values.update', $variationAttributeValue) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label for="variation_attribute_id" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Parent Attribute</label>
                            <select class="form-select form-select-lg border-0 bg-light rounded-3" id="variation_attribute_id" name="variation_attribute_id" required>
                                <option value="">-- Select Attribute --</option>
                                @foreach($attributes as $attribute)
                                    <option value="{{ $attribute->id }}" {{ old('variation_attribute_id', $variationAttributeValue->variation_attribute_id) == $attribute->id ? 'selected' : '' }}>{{ $attribute->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="value" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Value Name</label>
                            <input type="text" class="form-control form-control-lg border-0 bg-light rounded-3" id="value" name="value" value="{{ old('value', $variationAttributeValue->value) }}" required>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-4 pt-0 d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.variation-attribute-values.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                             Update Value
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
