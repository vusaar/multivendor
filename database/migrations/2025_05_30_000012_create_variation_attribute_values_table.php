<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 4. Variation Attribute Values Table
return new class extends Migration {
    public function up() {
        Schema::create('variation_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variation_attribute_id');
            $table->string('value'); // e.g., Red, Large
            $table->timestamps();
            $table->foreign('variation_attribute_id')->references('id')->on('variation_attributes')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('variation_attribute_values');
    }
};
