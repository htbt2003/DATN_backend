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
        Schema::create('db_config', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('email');
            $table->string('phone');
            $table->string('zalo');
            $table->string('facebook');
            $table->string('address');
            $table->string('youtube');
            $table->string('metadesc');
            $table->string('metakey');
            $table->timestamps(); //created_at, updated_at
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('status')->default(2);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('db_config');
    }
};
