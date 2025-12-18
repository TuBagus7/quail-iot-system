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
        Schema::create('item_dashboard', function (Blueprint $table) {
            $table->id();
            $table->float('gauge1')->default(0);
            $table->float('gauge2')->default(0);
            $table->float('gauge3')->default(0);
            $table->float('gauge4')->default(0);
            $table->float('gauge5')->default(0);
            $table->string('item_teks')->nullable();
            $table->boolean('status_buzzer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_dashboard');
    }
};
