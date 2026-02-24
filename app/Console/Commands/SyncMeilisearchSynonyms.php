<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterProduct;
use App\Models\Product;
use MeiliSearch\Client;

class SyncMeilisearchSynonyms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:sync-synonyms {--force : Force sync even if already synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync master product synonyms to Meilisearch index settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Syncing synonyms to Meilisearch...');

            // Get Meilisearch client using config
            $client = new \MeiliSearch\Client(
                config('scout.meilisearch.host'),
                config('scout.meilisearch.key')
            );
            $indexName = (new Product())->searchableAs();
            
            $this->info("Using index: {$indexName}");

            // Fetch master products with synonyms
            $query = MasterProduct::whereNotNull('synonyms')->where('synonyms', '!=', '');
            
            if (!$this->option('force')) {
                $query->where('is_synced', false);
            }
            
            $masterProducts = $query->get();

            if ($masterProducts->isEmpty()) {
                $this->info('No master products with unsynced synonyms found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$masterProducts->count()} master products with synonyms.");

            // Build synonym dictionary for Meilisearch
            $synonyms = [];
            
            foreach ($masterProducts as $masterProduct) {
                // Parse synonyms (comma-separated)
                $synonymList = array_map('trim', explode(',', $masterProduct->synonyms));
                
                // Add the master product name itself to the synonym list
                $allTerms = array_merge([strtolower($masterProduct->name)], array_map('strtolower', $synonymList));
                $allTerms = array_unique(array_filter($allTerms));
                
                if (count($allTerms) > 1) {
                    // Use the master product name as the key
                    $key = strtolower(str_replace(' ', '_', $masterProduct->name));
                    $synonyms[$key] = array_values($allTerms);
                    
                    $this->line("  {$masterProduct->name}: " . implode(', ', $synonymList));
                }
            }

            if (empty($synonyms)) {
                $this->warn('No valid synonyms to sync.');
                return Command::SUCCESS;
            }

            // Update Meilisearch index settings
            $this->info("\nUpdating Meilisearch index settings...");
            $index = $client->index($indexName);
            $index->updateSynonyms($synonyms);

            // Mark master products as synced
            $masterProducts->each(function ($masterProduct) {
                $masterProduct->update(['is_synced' => true]);
            });

            $this->info("\n✓ Successfully synced " . count($synonyms) . " synonym groups to Meilisearch!");
            $this->info("✓ Marked {$masterProducts->count()} master products as synced.");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error syncing synonyms: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
