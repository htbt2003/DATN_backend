<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('db_productsale', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->float('price_sale');
            $table->unsignedInteger('qty');
            $table->datetime('date_begin');
            $table->datetime('date_end');
            $table->timestamps(); //created_at, updated_at
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_productsale');
    }
};
