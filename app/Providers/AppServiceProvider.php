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
        //


          Builder::macro('orWherePGSimilarity', function (string $column, string $value) {
            $this->orWhereRaw("{$column} % ?", [$value]);
          });

          Builder::macro('wherePGSimilarity', function (string $column, string $value) {
            $this->whereRaw("{$column} % ?", [$value]);
          });
    }
}
