<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncProductEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-embeddings {--force : Re-generate embeddings even if they exist} {--async : Dispatch as background jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for products that are missing them';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = \App\Models\Product::query();

        if (!$this->option('force')) {
            $query->whereNull('embedding');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No products need embedding generation.');
            return Command::SUCCESS;
        }

        $this->info("Starting embedding generation for {$count} products...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunkById(100, function ($products) use ($bar) {
            foreach ($products as $product) {
                if ($this->option('async')) {
                    \App\Jobs\GenerateProductEmbedding::dispatch($product);
                } else {
                    \App\Jobs\GenerateProductEmbedding::dispatchSync($product);
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Embedding generation completed.');

        return Command::SUCCESS;
    }
}
