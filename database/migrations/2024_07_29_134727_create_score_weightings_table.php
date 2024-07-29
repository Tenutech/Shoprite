<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_weightings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('score_type')->unique('unique_score_type');
            $table->decimal('weight', 5);
            $table->decimal('max_value', 10)->nullable();
            $table->string('condition_field')->nullable();
            $table->string('condition_value')->nullable();
            $table->decimal('fallback_value', 5)->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score_weightings');
    }
};
