<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Variation Attribute Values') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Variation Attribute Values</h5>
                <a href="{{ route('admin.variation-attribute-values.create') }}" class="btn btn-success">Add Value</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-top mb-0">
                        <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($values as $value)
                                <tr>
                                    <td>{{ $value->attribute->name }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('admin.variation-attribute-values.edit', $value) }}" class="btn btn-outline-warning btn-sm">Edit</a>
                                        <form action="{{ route('admin.variation-attribute-values.destroy', $value) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this value?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No values found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $values->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
