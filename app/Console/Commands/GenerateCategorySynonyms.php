<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use App\Jobs\SyncCategoryData;

class GenerateCategorySynonyms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:generate-synonyms {--limit= : Limit the number of categories to process} {--force : Overwrite existing synonyms}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use AI (Gemini) to generate synonyms for categories that have none';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = Category::query();

        if (!$this->option('force')) {
            $query->whereNull('synonyms')->orWhere('synonyms', '[]');
        }

        if ($limit = $this->option('limit')) {
            $query->limit($limit);
        }

        $categories = $query->get();
        $count = $categories->count();

        if ($count === 0) {
            $this->info('No categories need synonym generation.');
            return Command::SUCCESS;
        }

        $this->info("Starting synonym generation for {$count} categories...");
        $agentUrl = rtrim(env('HYBRID_SEARCH_URL', 'http://127.0.0.1:3002/api/search'), '/search');
        $endpoint = str_replace('localhost', '127.0.0.1', $agentUrl) . '/categories/suggest-synonyms';

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($categories as $category) {
            try {
                $response = Http::post($endpoint, ['name' => $category->name]);

                if ($response->successful()) {
                    $synonyms = $response->json('synonyms');
                    $category->update(['synonyms' => $synonyms]);
                    
                    // Trigger vector re-indexing for this category too
                    SyncCategoryData::dispatchSync($category);
                } else {
                    $this->error("\nFailed to generate synonyms for {$category->name}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("\nException for {$category->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Synonym generation completed.');

        return Command::SUCCESS;
    }
}
