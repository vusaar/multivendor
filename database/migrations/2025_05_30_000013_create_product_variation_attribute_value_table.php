<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 5. Pivot Table: Product Variation Attribute Value
return new class extends Migration {
    public function up() {
        Schema::create('product_variation_attribute_value', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variation_id');
            $table->unsignedBigInteger('variation_attribute_value_id');
            $table->timestamps();
            $table->foreign('product_variation_id', 'pvav_variation_id_fk')->references('id')->on('product_variations')->onDelete('cascade');
            $table->foreign('variation_attribute_value_id', 'pvav_attr_value_id_fk')->references('id')->on('variation_attribute_values')->onDelete('cascade');
            $table->unique(['product_variation_id', 'variation_attribute_value_id'], 'pvav_unique');
        });
    }
    public function down() {
        Schema::dropIfExists('product_variation_attribute_value');
    }
};
