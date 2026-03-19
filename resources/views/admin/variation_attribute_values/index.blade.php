<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Variation Attribute Values') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Variation Attribute Values</h4>
                <a href="{{ route('admin.variation-attribute-values.create') }}" class="btn btn-primary">
                    <i class="cil-plus"></i> Add Value
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Attribute</th>
                                <th>Value</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($values as $value)
                            <tr>
                                <td class="ps-4"><span class="text-muted small">{{ $value->attribute->name }}</span></td>
                                <td><span class="fw-bold text-dark">{{ $value->value }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.variation-attribute-values.edit', $value) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.variation-attribute-values.destroy', $value) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this value?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                                <i class="cil-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">No values found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                {{ $values->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</x-app-layout>
