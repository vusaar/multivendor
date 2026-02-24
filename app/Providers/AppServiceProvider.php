<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
             custome macros for pgsql similarity operator using pg_trgm extension
             to be used in eloquent query builder
             e.g. Model::wherePGSimilarity('column', 'value')->get();
             e.g. Model::orWherePGSimilarity('column', 'value')->get();
        */

          Builder::macro('orWherePGSimilarity', function (string $column, string $value) {
            $this->orWhereRaw("{$column} % ?", [$value]);
          });

          Builder::macro('wherePGSimilarity', function (string $column, string $value) {
            $this->whereRaw("{$column} % ?", [$value]);
          });

          // Register MasterProduct observer
          \App\Models\MasterProduct::observe(\App\Observers\MasterProductObserver::class);
          
    }
}
