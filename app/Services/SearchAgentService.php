<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchAgentService
{
    protected $url;

    public function __construct()
    {
        $this->url = env('HYBRID_SEARCH_URL', 'http://localhost:3001/api/search');
    }

    /**
     * Send a query to the Hybrid Search Agent.
     *
     * @param string $query
     * @return array|null List of product IDs or null on failure
     */
    public function search($query)
    {
        try {
            Log::info("Calling Hybrid Search Agent for: {$query}");

            $response = Http::timeout(60)->post($this->url, [
                'query' => $query,
                'userId' => auth()->id() ?? 'guest'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // The agent returns { statusToSuccess: true, data: { results: [...] } }
                // where results is an array of objects like { id: "28", ... }
                $results = $data['data']['results'] ?? [];

                if (isset($results['status']) && $results['status'] === 'no_results') {
                    Log::info("Hybrid Search: No results found for '{$query}'");
                    return [];
                }

                if (is_array($results)) {
                    // Return both ID and RRF score, ensuring ID is numeric
                    return collect($results)
                        ->filter(fn($result) => isset($result['id']) && is_numeric($result['id']))
                        ->map(function ($result) {
                            return [
                                'id' => (int)$result['id'],
                                'score' => $result['rrf_score'] ?? 0
                            ];
                        })->toArray();
                }
            }

            Log::error("Hybrid Search Agent Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Hybrid Search Exception: " . $e->getMessage());
            return null;
        }
    }
}
