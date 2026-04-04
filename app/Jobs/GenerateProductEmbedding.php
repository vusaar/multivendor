<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProductEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Illuminate\Support\Facades\Log::info("GenerateProductEmbedding: handling product {$this->product->id}");
        $this->product->load(['vendor', 'category', 'brand', 'variations.attributeValues']);

        $agentUrl = rtrim(env('HYBRID_SEARCH_URL', 'http://localhost:3002/api/search'), '/search');
        $endpoint = $agentUrl . '/embeddings/format-and-generate';

        $payload = [
            'product' => [
                'name' => $this->product->name,
                'description' => $this->product->description,
                'category_name' => $this->product->category->name ?? null,
                'category_synonyms' => $this->product->category->synonyms ?? null,
                'parent_category_name' => $this->product->category->parent->name ?? null,
                'parent_category_synonyms' => $this->product->category->parent->synonyms ?? null,
                'brand_name' => $this->product->brand->name ?? null,
                'variations' => $this->product->variations->map(function ($v) {
                    return ['value' => $v->attributeValues->pluck('value')->join(', ')];
                })->toArray(),
            ]
        ];
        
        try {
            $response = \Illuminate\Support\Facades\Http::post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $embeddingArray = $data['embedding'];
                $formattedText = $data['formatted_text'];

                // Convert array to pgvector string format: [1.2, 3.4, ...]
                $embeddingString = '[' . implode(',', $embeddingArray) . ']';

                // Update search_context via Eloquent (safer for long text)
                $this->product->search_context = $formattedText;
                $this->product->needs_reindex = false; // Reset the flag
                $this->product->saveQuietly();
                \Illuminate\Support\Facades\Log::info("GenerateProductEmbedding: search_context saved for product {$this->product->id}");

                // Update embedding via raw SQL (required for ::vector cast)
                $affected = \Illuminate\Support\Facades\DB::update(
                    "UPDATE products SET embedding = ?::vector WHERE id = ?",
                    [$embeddingString, $this->product->id]
                );

                if ($affected) {
                    \Illuminate\Support\Facades\Log::info("GenerateProductEmbedding: vector saved for product {$this->product->id}");
                } else {
                    \Illuminate\Support\Facades\Log::warning("GenerateProductEmbedding: vector NOT updated for product {$this->product->id} (0 rows affected)");
                }
            } else {
                \Illuminate\Support\Facades\Log::error("Embedding job failed for product {$this->product->id}: " . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ACTUAL_ERROR: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("Embedding job Exception for product {$this->product->id}: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
        }
    }
}
