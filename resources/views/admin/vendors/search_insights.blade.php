<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-bold text-dark">
                Search Insights: <span class="text-primary">{{ $vendor->shop_name }}</span>
            </h2>
            <div class="text-muted small fw-600">
                <i class="cil-calendar"></i> Last 30 Days
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <!-- Summary Stats -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100 overflow-hidden" style="position: relative;">
                        <div class="stat-label">Total Search Appearances</div>
                        <div class="stat-value text-emerald">{{ $totalAppearances }}</div>
                        <div class="stat-trend small text-muted">Times your products were seen</div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100 overflow-hidden" style="position: relative;">
                        <div class="stat-label">Discovery Rate</div>
                        <div class="stat-value text-primary">{{ $sortedInsights->count() }}</div>
                        <div class="stat-trend small text-muted">Unique keywords driving traffic</div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-tags"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card stat-widget h-100 overflow-hidden" style="position: relative;">
                        <div class="stat-label">Avg. Visibility Rank</div>
                        <div class="stat-value text-indigo">
                            {{ $totalAppearances > 0 ? round($sortedInsights->avg('avg_rank'), 1) : 'N/A' }}
                        </div>
                        <div class="stat-trend small text-muted">Average position in results</div>
                        <div style="position: absolute; bottom: 0; right: 0; opacity: 0.1; font-size: 5rem; margin-bottom: -1rem; margin-right: -0.5rem;">
                            <i class="cil-list-numbered"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Keyword Performance Table -->
                <div class="col-lg-8">
                    <div class="glass-card p-0 overflow-hidden h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-2">
                            <h5 class="fw-bold mb-0">Keyword Performance</h5>
                            <p class="text-muted small">Queries that triggered your products in search results</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0 small text-uppercase fw-800 text-muted">Query</th>
                                        <th class="border-0 small text-uppercase fw-800 text-muted">Appearances</th>
                                        <th class="border-0 small text-uppercase fw-800 text-muted">Avg. Rank</th>
                                        <th class="border-0 small text-uppercase fw-800 text-muted pe-4">Top Match</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sortedInsights as $insight)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ ucfirst($insight['query']) }}</div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-light text-dark border px-3">
                                                {{ $insight['count'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-600 me-2">{{ $insight['avg_rank'] }}</span>
                                                <div class="progress w-100" style="height: 4px; max-width: 60px;">
                                                    <div class="progress-bar bg-primary" style="width:{{ 100 - ($insight['avg_rank'] * 10) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            @foreach($insight['top_product_ids'] as $pid)
                                                <div class="small text-muted text-truncate" style="max-width: 150px;">
                                                    • {{ $productNames[$pid] ?? 'Unknown Product' }}
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">No search data available for the last 30 days.</div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Strategic Insights -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 mb-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="cil-star text-warning me-2"></i> Most Valuable Keywords
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($topKeywords as $tk)
                                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-3 border border-primary-subtle">
                                    {{ $tk['query'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-card p-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="cil-lightbulb text-info me-2"></i> Optimization Tips
                        </h6>
                        <ul class="list-unstyled small text-muted">
                            <li class="mb-3">
                                <i class="cil-check-circle text-success me-2"></i>
                                <strong>High Rank keywords:</strong> Your products are appearing at the top. Ensure these product descriptions are polished!
                            </li>
                            <li class="mb-3">
                                <i class="cil-warning text-warning me-2"></i>
                                <strong>Low Rank keywords:</strong> Your products appear late. Consider adding these keywords to your product titles.
                            </li>
                            <li>
                                <i class="cil-info text-primary me-2"></i>
                                Top keywords are based on how many unique users saw your store.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-primary { background-color: rgba(var(--primary-rgb), 0.1); }
        .fw-800 { font-weight: 800; }
        .fw-600 { font-weight: 600; }
    </style>
</x-app-layout>
