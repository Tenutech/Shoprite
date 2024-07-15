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
        Schema::create('position_tag', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedBigInteger('position_id')->nullable()->index('position_tag_position_id_foreign');
            $table->unsignedBigInteger('tag_id')->nullable()->index('position_tag_tag_id_foreign');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_tag');
    }
};
