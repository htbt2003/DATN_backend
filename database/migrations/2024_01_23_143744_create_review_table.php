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
        Schema::create('db_review', function (Blueprint $table) {
            $table->id();
            $table->interger('user_id');
            $table->interger('product_id');
            $table->interger('rating');
            $table->string('comment');
            $table->timestamps(); //created_at, updated_at
            $table->unsignedInteger('created_by')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_review');
    }
};
