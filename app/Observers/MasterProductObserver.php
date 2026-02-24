<?php

namespace App\Observers;

use App\Models\MasterProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;

class MasterProductObserver
{
    /**
     * Handle the MasterProduct "created" event.
     *
     * @param  \App\Models\MasterProduct  $masterProduct
     * @return void
     */
    public function created(MasterProduct $masterProduct)
    {
        // Mark as unsynced when created with synonyms
        if ($masterProduct->synonyms) {
            $masterProduct->is_synced = false;
            $masterProduct->saveQuietly();
        }
    }

    /**
     * Handle the MasterProduct "updated" event.
     *
     * @param  \App\Models\MasterProduct  $masterProduct
     * @return void
     */
    public function updated(MasterProduct $masterProduct)
    {
        // If synonyms were changed, mark as unsynced
        if ($masterProduct->isDirty('synonyms')) {
            $masterProduct->is_synced = false;
            $masterProduct->saveQuietly();
            
            // Optionally trigger automatic sync in background
            // You can uncomment this to enable automatic syncing
            // \Illuminate\Support\Facades\Artisan::call('meilisearch:sync-synonyms');
        }
    }

    /**
     * Handle the MasterProduct "deleted" event.
     *
     * @param  \App\Models\MasterProduct  $masterProduct
     * @return void
     */
    public function deleted(MasterProduct $masterProduct)
    {
        // When a master product is deleted, you might want to re-sync
        // to remove its synonyms from Meilisearch
    }

    /**
     * Handle the MasterProduct "restored" event.
     *
     * @param  \App\Models\MasterProduct  $masterProduct
     * @return void
     */
    public function restored(MasterProduct $masterProduct)
    {
        //
    }

    /**
     * Handle the MasterProduct "force deleted" event.
     *
     * @param  \App\Models\MasterProduct  $masterProduct
     * @return void
     */
    public function forceDeleted(MasterProduct $masterProduct)
    {
        //
    }
}
