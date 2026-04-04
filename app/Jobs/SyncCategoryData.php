<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCategoryData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $agentUrl = rtrim(env('HYBRID_SEARCH_URL', 'http://localhost:3002/api/search'), '/search');
        $endpoint = $agentUrl . '/categories/sync';

        $payload = [
            'id' => $this->category->id,
            'name' => $this->category->name,
            'synonyms' => $this->category->synonyms,
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::post($endpoint, $payload);

            if ($response->successful()) {
                \Illuminate\Support\Facades\Log::info("SyncCategoryData: successfully synced category {$this->category->id}");
            } else {
                \Illuminate\Support\Facades\Log::error("SyncCategoryData failed for category {$this->category->id}: " . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncCategoryData Exception for category {$this->category->id}: " . $e->getMessage());
        }
    }
}
