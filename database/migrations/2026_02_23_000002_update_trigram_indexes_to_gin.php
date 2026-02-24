<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade Trigram Indexes from GIST to GIN (Faster for reads)
        DB::statement('DROP INDEX IF EXISTS products_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_description_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_search_context_trgm_idx');

        DB::statement('CREATE INDEX products_name_gin_trgm_idx ON products USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX products_description_gin_trgm_idx ON products USING gin (description gin_trgm_ops)');
        DB::statement('CREATE INDEX products_search_context_gin_trgm_idx ON products USING gin (search_context gin_trgm_ops)');

        // 2. Add B-Tree indexes for frequently joined Foreign Keys to speed up the storefront search joins
        Schema::table('products', function (Blueprint $table) {
            $table->index('vendor_id', 'products_vendor_id_idx');
            $table->index('category_id', 'products_category_id_idx');
            $table->index('brand_id', 'products_brand_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_name_gin_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_description_gin_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_search_context_gin_trgm_idx');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_vendor_id_idx');
            $table->dropIndex('products_category_id_idx');
            $table->dropIndex('products_brand_id_idx');
        });

        // Recreate GIST if needed, but usually down should revert cleanly.
    }
};
