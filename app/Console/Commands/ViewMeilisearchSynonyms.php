<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ViewMeilisearchSynonyms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:view-synonyms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View current synonyms configured in Meilisearch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Fetching synonyms from Meilisearch...');

            // Get Meilisearch client
            $client = new \MeiliSearch\Client(
                config('scout.meilisearch.host'),
                config('scout.meilisearch.key')
            );
            $indexName = (new Product())->searchableAs();
            
            $this->info("Index: {$indexName}\n");

            // Get synonyms from Meilisearch
            $index = $client->index($indexName);
            $synonyms = $index->getSynonyms();

            if (empty($synonyms)) {
                $this->warn('No synonyms configured in Meilisearch.');
                return Command::SUCCESS;
            }

            $this->info("Found " . count($synonyms) . " synonym groups:\n");

            // Display synonyms in a table
            $rows = [];
            foreach ($synonyms as $key => $terms) {
                $rows[] = [
                    $key,
                    implode(', ', $terms)
                ];
            }

            $this->table(['Synonym Group', 'Terms'], $rows);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error fetching synonyms: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
