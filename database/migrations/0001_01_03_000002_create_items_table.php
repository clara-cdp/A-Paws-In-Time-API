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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('css_id', 45);
            $table->longText('description');
            $table->string('image_url', 150)->nullable();

            $table->boolean('is_portable')->default(false); 
            $table->boolean('is_visible')->default(true);   

            $table->foreignId('room_id')->nullable()->constrained();
            $table->foreignId('interaction_id')->nullable()->constrained();
            $table->foreignId('game_id')->nullable()->constrained()->cascadeOnDelete();

            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
