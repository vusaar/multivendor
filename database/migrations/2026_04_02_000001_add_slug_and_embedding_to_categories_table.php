<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Disable transaction for this migration to allow ALTER TYPE ... ADD VALUE
     * which cannot be executed inside a transaction block in PostgreSQL.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Handle Status Constraint/Type (to allow 'active' status)
        try {
            // Drop common check constraint names created by Laravel for'status'
            DB::statement("ALTER TABLE categories DROP CONSTRAINT IF EXISTS categories_status_check");
            
            // Also handle native enum if it exists
            try {
                DB::statement("ALTER TYPE categories_status_enum ADD VALUE IF NOT EXISTS 'active'");
            } catch (\Exception $e) {}

            // Ensure column is varchar to avoid any other enum-related restrictions
            DB::statement("ALTER TABLE categories ALTER COLUMN status TYPE VARCHAR(255)");
        } catch (\Exception $e) {
            // Log or ignore if table/columns don't exist yet
        }

        // 2. Ensure columns exist
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        if (!Schema::hasColumn('categories', 'embedding')) {
            try {
                DB::statement('ALTER TABLE categories ADD COLUMN embedding vector(3072) NULL');
            } catch (\Exception $e) {}
        }

        // 3. Generate slugs and update status for existing categories
        $categories = DB::table('categories')->get();
        foreach ($categories as $category) {
            $slug = $category->slug ?: Str::slug($category->name ?: 'category-' . $category->id);
            
            // Ensure slug is unique
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            DB::table('categories')->where('id', $category->id)->update([
                'slug' => $slug,
                'status' => 'active'
            ]);
        }

        // 4. Finalize slug constraints
        try {
            DB::statement('ALTER TABLE categories ALTER COLUMN slug SET NOT NULL');
            DB::statement('ALTER TABLE categories ADD CONSTRAINT categories_slug_unique UNIQUE (slug)');
        } catch (\Exception $e) {
            // Constraint or NOT NULL might already exist
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('categories', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });
    }
};
