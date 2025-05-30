<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 3. Variation Attributes Table
return new class extends Migration {
    public function up() {
        Schema::create('variation_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Color, Size
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('variation_attributes');
    }
};
