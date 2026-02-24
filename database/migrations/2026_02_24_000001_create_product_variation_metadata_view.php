<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the Materialized View for Variation Metadata
        // We aggregate all variation attributes for a single product into one string
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS product_variation_metadata AS
            SELECT 
                pv.product_id,
                STRING_AGG(DISTINCT va.name || ': ' || vav.value, ' ') AS variation_search_text
            FROM product_variations pv
            JOIN product_variation_attribute_value pvav ON pv.id = pvav.product_variation_id
            JOIN variation_attribute_values vav ON pvav.variation_attribute_value_id = vav.id
            JOIN variation_attributes va ON vav.variation_attribute_id = va.id
            GROUP BY pv.product_id
        ");

        // 2. Add GIN Trigram index to the view for fast similarity searching
        DB::statement('CREATE INDEX IF NOT EXISTS variation_metadata_gin_trgm_idx ON product_variation_metadata USING gin (variation_search_text gin_trgm_ops)');
        
        // 3. Add a unique index on product_id to allow CONCURRENT refresh
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS variation_metadata_product_id_idx ON product_variation_metadata (product_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS product_variation_metadata');
    }
};
