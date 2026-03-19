<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmbeddingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function saving_a_product_triggers_embedding_generation()
    {
        // Mock the Agent API
        Http::fake([
            '*/api/embeddings/format-and-generate' => Http::response([
                'status' => 'success',
                'formatted_text' => 'Name: Test Product | CategoryPath: Uncategorized | Description: Test Desc',
                'embedding' => array_fill(0, 1536, 0.1) // Mocked vector
            ], 200),
        ]);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Desc'
        ]);

        // Refresh from DB to see if job updated it (queue is sync)
        $product->refresh();

        $this->assertNotNull($product->embedding);
        $this->assertNotNull($product->search_context);
        $this->assertStringContainsString('Test Product', $product->search_context);
    }
}
