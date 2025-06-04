<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Variation Attributes') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Variation Attributes</h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAttributeModal">Add Attribute</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-top mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Values</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attributes as $attribute)
                                <tr>
                                    <td>{{ $attribute->name }}</td>
                                    <td>
                                        @foreach($attribute->values as $val)
                                            <span class="badge bg-secondary">{{ $val->value }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.variation-attributes.edit', $attribute) }}" class="btn btn-outline-warning btn-sm">Edit</a>
                                        <form action="{{ route('admin.variation-attributes.destroy', $attribute) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this attribute?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No attributes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $attributes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Add Attribute Modal -->
    <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAttributeModalLabel">Add Variation Attribute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.variation-attributes.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Attribute Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="values" class="form-label">Attribute Values</label>
                            <div id="values-list">
                                <input type="text" class="form-control mb-2" name="values[]" placeholder="Enter value" required>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-value-btn">Add Another Value</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Attribute</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addValueBtn = document.getElementById('add-value-btn');
            if (addValueBtn) {
                addValueBtn.addEventListener('click', function() {
                    var container = document.getElementById('values-list');
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control mb-2';
                    input.name = 'values[]';
                    input.placeholder = 'Enter value';
                    input.required = true;
                    container.appendChild(input);
                });
            }
        });
    </script>
   
</x-app-layout>
