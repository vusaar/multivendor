<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Category Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="glass-card mb-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
            <h4 class="mb-0 fw-bold" style="color:var(--midnight)">Categories</h4>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="cil-plus"></i> New Category
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">Category Name</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted">Hierarchy</th>
                            <th class="border-0 text-uppercase small fw-bold text-muted">Description</th>
                            <th class="pe-4 border-0 text-uppercase small fw-bold text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $category->name }}</td>
                                <td>
                                    @php
                                        $parents = [];
                                        $current = $category->parent;
                                        while ($current) {
                                            array_unshift($parents, $current->name);
                                            $current = $current->parent;
                                        }
                                    @endphp
                                    @if(count($parents))
                                        <span class="text-muted small">{{ implode(' › ', $parents) }} › </span>
                                        <span class="fw-bold text-primary small">{{ $category->name }}</span>
                                    @else
                                        <span class="badge badge-glass font-monospace">Root</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ Str::limit($category->description, 50) }}</td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-action btn-action-edit" title="Edit">
                                            <i class="cil-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this category?')">
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
                                <td colspan="4" class="text-center py-5 text-muted">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 p-4">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
