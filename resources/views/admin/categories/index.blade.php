<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Category Listing') }}
        </h2>
    </x-slot>
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Categories</h5>
            <a href="{{ route('admin.categories.create') }}" class="btn action-btn product-action-btn btn-new mb-3"><i class="cil-plus"></i> New Category</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x:unset; padding: 1rem 1rem;">
                <table class="table table-striped table-hover align-top border mb-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Parent Category</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @php
                                        $parents = [];
                                        $current = $category->parent;
                                        while ($current) {
                                            array_unshift($parents, $current->name);
                                            $current = $current->parent;
                                        }
                                    @endphp
                                    {{ count($parents) ? implode(' / ', $parents) : '-' }}
                                </td>
                                <td>{{ $category->description }}</td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm action-btn edit-btn"><i class="cil-pencil"></i> </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm action-btn delete-btn"><i class="cil-trash"></i> </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
</x-app-layout>
