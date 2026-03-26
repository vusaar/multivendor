<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Variation Attributes') }}
        </h2>
    </x-slot>
    <div class="container-fluid py-4">
        <div class="glass-card mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <h4 class="mb-0 fw-bold" style="color: var(--midnight)">Variation Attributes</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                    <i class="cil-plus"></i> Add Attribute
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Attribute Name</th>
                                <th>Status</th>
                                <th>Values</th>
                                <th class="text-end pe-4" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attributes as $attribute)
                            <tr>
                                <td class="ps-4"><span class="fw-bold text-dark">{{ $attribute->name }}</span></td>
                                <td>
                                    @if($attribute->status == 'approved')
                                        <span class="badge bg-success-subtle text-success px-3 rounded-pill fw-bold" style="font-size: 0.7rem;">APPROVED</span>
                                    @elseif($attribute->status == 'pending')
                                        <span class="badge bg-warning-subtle text-warning px-3 rounded-pill fw-bold" style="font-size: 0.7rem;">PENDING</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 rounded-pill fw-bold" style="font-size: 0.7rem;">REJECTED</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($attribute->values as $val)
                                            <span class="badge badge-crimson">{{ $val->value }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @php
                                            $canManage = auth()->user()->hasRole('super.admin') || 
                                                        (auth()->user()->hasRole('vendor.admin') && $attribute->vendor_id == ($vendor->id ?? null));
                                        @endphp

                                        @if($attribute->status == 'pending' && auth()->user()->hasRole('super.admin'))
                                            <form action="{{ route('admin.variation-attributes.approve', $attribute) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action btn-action-approve" title="Approve">
                                                    <i class="cil-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($canManage)
                                            <a href="{{ route('admin.variation-attributes.edit', $attribute) }}" class="btn-action btn-action-edit" title="Edit">
                                                <i class="cil-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.variation-attributes.destroy', $attribute) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this attribute?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                                    <i class="cil-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small italic">System Item</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">No attributes found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 px-4 pb-4">
                {{ $attributes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Add Attribute Modal -->
    <div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold" id="addAttributeModalLabel" style="color: var(--midnight)">Add Variation Attribute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.variation-attributes.store') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Attribute Name</label>
                            <input type="text" class="form-control form-control-lg border-0 bg-light rounded-3" id="modal-name" name="name" placeholder="e.g., Color or Size" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="values" class="form-label fw-600 small text-uppercase tracking-wider text-muted">Attribute Values</label>
                            <div id="modal-values-list">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control border-0 bg-light rounded-3" name="values[]" placeholder="Enter value" required>
                                </div>
                            </div>
                            <button type="button" class="btn btn-link text-primary p-0 fw-600 small" id="modal-add-value-btn" style="text-decoration: none;">
                                <i class="cil-plus"></i> Add Another Value
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4 rounded-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold shadow-sm">Add Attribute</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addValueBtn = document.getElementById('modal-add-value-btn');
            if (addValueBtn) {
                addValueBtn.addEventListener('click', function() {
                    var container = document.getElementById('modal-values-list');
                    var div = document.createElement('div');
                    div.className = 'input-group mb-2';
                    div.innerHTML = '<input type="text" class="form-control border-0 bg-light rounded-3 mt-2" name="values[]" placeholder="Enter value" required>';
                    container.appendChild(div);
                });
            }
        });
    </script>
   
</x-app-layout>
