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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->string('verb_trigger', 45);
            $table->integer('step_required')->default(0);
            $table->integer('next_step')->default(0);
            $table->longText('reward')->nullable();
     
            $table->foreignId('item_id')->constrained('items'); 
            $table->foreignId('required_item_id')->nullable()->constrained('items'); 
            $table->foreignId('unlocked_item_id')->nullable()->constrained('items');
            $table->foreignId('target_room_id')->nullable()->constrained('rooms');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
