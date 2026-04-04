<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Jobs\SyncCategoryData;

class SyncCategoryEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:sync-embeddings {--async : Dispatch as background jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all categories with the search agent for synonym and vector indexing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $categories = Category::all();
        $count = $categories->count();

        if ($count === 0) {
            $this->info('No categories found.');
            return Command::SUCCESS;
        }

        $this->info("Starting synchronization for {$count} categories...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($categories as $category) {
            if ($this->option('async')) {
                SyncCategoryData::dispatch($category);
            } else {
                SyncCategoryData::dispatchSync($category);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Category synchronization completed.');

        return Command::SUCCESS;
    }
}
