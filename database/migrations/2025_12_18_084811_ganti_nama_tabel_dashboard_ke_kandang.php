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
        Schema::rename('item_dashboard', 'data_kandang');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('data_kandang', 'item_dashboard');
    }
};
