<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search Analytics') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="glass-card mb-4 overflow-hidden shadow-sm border-0">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between p-4">
                <div>
                    <h4 class="mb-0 fw-bold" style="color:var(--midnight)">Search Logs</h4>
                    <p class="text-muted small mb-0">Track AI interpretation and result quality</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-soft-info text-info p-2 px-3 rounded-pill">
                        <i class="cil-chart"></i> Total: {{ $logs->total() }}
                    </span>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 text-uppercase small fw-bold text-muted">User & Time</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">Search Query</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted">AI Interpretation</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted text-center">Results</th>
                                <th class="border-0 text-uppercase small fw-bold text-muted text-center">Latency</th>
                                <th class="pe-4 border-0 text-uppercase small fw-bold text-muted text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $log->phone_number ?: 'Guest' }}</div>
                                        <div class="small text-muted">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium">"{{ $log->query }}"</span>
                                        @if($log->corrected_query && $log->corrected_query !== $log->query)
                                            <div class="small text-primary italic mt-1">
                                                <i class="cil-check-circle"></i> AI Fix: "{{ $log->corrected_query }}"
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->intent)
                                            <div class="d-flex flex-wrap gap-1">
                                                @if(!empty($log->intent['entity']))
                                                    <span class="badge bg-secondary text-white small" title="Entity">
                                                        {{ $log->intent['entity'] }}
                                                    </span>
                                                @endif
                                                @if(!empty($log->intent['categories']))
                                                    @foreach($log->intent['categories'] as $cat)
                                                        <span class="badge bg-outline-primary text-primary border border-primary small" style="font-size: 0.7rem">
                                                            {{ $cat }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted italic small">No intent extracted</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $log->results_count > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                                            {{ $log->results_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $color = 'text-success';
                                            if($log->duration_ms > 1000) $color = 'text-warning';
                                            if($log->duration_ms > 3000) $color = 'text-danger';
                                        @endphp
                                        <span class="fw-bold {{ $color }}">
                                            {{ $log->duration_ms }}ms
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-light border" 
                                                onclick="viewLogDetails({{ $log->id }})"
                                                title="View Raw Data">
                                            <i class="cil-info"></i> JSON
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="cil-search" style="font-size: 2rem"></i></div>
                                        No search logs recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 p-4">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold">Search Technical Details</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="mb-4">
                        <label class="text-uppercase small fw-bold text-muted mb-2 d-block">Search Core Info</label>
                        <div class="bg-dark p-3 rounded mb-2">
                             <div class="small text-white-50">Search ID: <span id="logSearchId" class="text-warning"></span></div>
                             <div class="small text-white-50 mt-1">Original Query: <span id="logQuery" class="text-info"></span></div>
                             <div class="small text-white-50 mt-1">AI Corrected: <span id="logCorrected" class="text-success"></span></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="text-uppercase small fw-bold text-muted mb-2 d-block">AI Extraction (Intent)</label>
                        <pre id="intentJson" class="bg-dark text-success p-3 rounded overflow-auto" style="max-height: 200px; font-size: 0.8rem;"></pre>
                    </div>
                    <div>
                        <label class="text-uppercase small fw-bold text-muted mb-2 d-block">Top Results (Rank & Score)</label>
                        <pre id="resultsJson" class="bg-dark text-info p-3 rounded overflow-auto" style="max-height: 300px; font-size: 0.8rem;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let technicalModal = null;
        
        function viewLogDetails(id) {
            fetch(`/admin/search-logs/${id}`)
                .then(response => response.json())
                .then(log => {
                    document.getElementById('logSearchId').textContent = log.search_id || 'N/A';
                    document.getElementById('logQuery').textContent = log.query || 'N/A';
                    document.getElementById('logCorrected').textContent = log.corrected_query || log.query || 'N/A';
                    
                    document.getElementById('intentJson').textContent = JSON.stringify(log.intent, null, 2);
                    document.getElementById('resultsJson').textContent = JSON.stringify(log.results, null, 2);
                    
                    if(!technicalModal) {
                        // CoreUI 5 uses 'coreui' namespace instead of 'bootstrap'
                        const ModalClass = (typeof coreui !== 'undefined') ? coreui.Modal : bootstrap.Modal;
                        technicalModal = new ModalClass(document.getElementById('logDetailsModal'));
                    }
                    technicalModal.show();
                })
                .catch(err => {
                    console.error('Error fetching log details:', err);
                    alert('Could not fetch technical details. Check console for error.');
                });
        }
    </script>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
        }
        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }
        .bg-outline-primary {
            background-color: transparent;
        }
    </style>
</x-app-layout>
