<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search Debugger') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="cil-search me-2"></i> Query Logs
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="padding: 0 1rem;">
                    <table class="table table-striped table-hover align-top border my-3" style="font-size: 0.85rem; table-layout: fixed; word-break: break-word;">
                        <thead>
                            <tr>
                                <th style="width: 160px;">Time</th>
                                <th style="width: 150px;">Source</th>
                                <th style="width: 200px;">User Query</th>
                                <th>Structured Input</th>
                                <th style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @php
                                    $data = is_array($log->message) ? $log->message : json_decode($log->message, true);
                                @endphp
                                <tr>
                                    <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $log->source }}</span>
                                    </td>
                                    <td><strong>{{ $data['user_query'] ?? '-' }}</strong></td>
                                    <td>
                                        <details class="mt-1">
                                            <summary class="text-primary" style="cursor: pointer; font-weight: 500;">
                                                <i class="cil-chevron-bottom me-1"></i> View Parameters
                                            </summary>
                                            <pre class="bg-light p-2 mt-2 border rounded" style="font-size: 0.75rem; max-height: 200px; overflow: auto;">{{ json_encode($data['structured_query'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    </td>
                                    <td>
                                        <button class="btn product-action-btn btn-outline-primary btn-sm btn-replay" data-id="{{ $log->id }}" title="Replay & Analyze">
                                            <i class="cil-media-play me-1"></i> Replay
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Debug Modal -->
    <div class="modal fade" id="debugModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title">
                        <i class="cil-bug me-2"></i> Search Diagnostics
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 text-primary">Raw Meilisearch Response</h6>
                                </div>
                                <div class="card-body p-0">
                                    <pre id="raw-output" class="p-3 m-0" style="height: 600px; overflow: auto; font-size: 0.75rem;"></pre>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 text-success">Final API Response</h6>
                                </div>
                                <div class="card-body p-0">
                                    <pre id="api-output" class="p-3 m-0" style="height: 600px; overflow: auto; font-size: 0.75rem;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .product-action-btn {
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
            color: #6c757d !important;
        }
        .product-action-btn i {
            color: #6c757d !important;
        }
        .product-action-btn:hover {
            background-color: #e2e6ea !important;
            color: #495057 !important;
        }
        .product-action-btn.btn-outline-primary:hover {
            background-color: #343a40 !important;
            border-color: #23272b !important;
            color: #fff !important;
        }
        .product-action-btn.btn-outline-primary:hover i {
            color: #fff !important;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
        details[open] summary i {
            transform: rotate(180deg);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const replayButtons = document.querySelectorAll('.btn-replay');
            const debugModalEl = document.getElementById('debugModal');
            const debugModal = coreui.Modal.getOrCreateInstance(debugModalEl);
            const rawOutput = document.getElementById('raw-output');
            const apiOutput = document.getElementById('api-output');

            replayButtons.forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.getAttribute('data-id');
                    rawOutput.textContent = 'Analyzing request...';
                    apiOutput.textContent = 'Fetching results...';
                    debugModal.show();

                    try {
                        const response = await fetch(`{{ url('admin/search-debug/replay') }}/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        rawOutput.textContent = JSON.stringify(data.raw_meilisearch, null, 2);
                        apiOutput.textContent = JSON.stringify(data.api_response, null, 2);
                    } catch (error) {
                        rawOutput.textContent = 'Diagnostic Error: ' + error.message;
                        apiOutput.textContent = 'Failed to retrieve response.';
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
