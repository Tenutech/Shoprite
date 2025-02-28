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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onUpdate('cascade')->onDelete('set null');
            $table->decimal('value', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
