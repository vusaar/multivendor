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
        // Enable the pg_trgm extension if it doesn't exist
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // Add GIST trigram indexes for faster similarity searching
        DB::statement('CREATE INDEX IF NOT EXISTS products_name_trgm_idx ON products USING gist (name gist_trgm_ops)');
        // DB::statement("CREATE INDEX trigram_idx_search_context ON products USING gist (search_context gist_trgm_ops)");
        DB::statement('CREATE INDEX IF NOT EXISTS products_description_trgm_idx ON products USING gist (description gist_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_description_trgm_idx');
        // DB::statement('DROP INDEX IF EXISTS products_search_context_trgm_idx');
    }
};
