<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('search_context')->nullable();
        });

        // Use raw SQL for vector type as standard Schema doesn't support it directly in older Laravel/Postgres combinations easily
        DB::statement('ALTER TABLE products ADD COLUMN embedding vector(3072)');
        
        // Create index for search context
        DB::statement('CREATE INDEX products_search_context_gin_trgm_idx ON products USING gin (search_context gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['search_context', 'embedding']);
        });
    }
};
