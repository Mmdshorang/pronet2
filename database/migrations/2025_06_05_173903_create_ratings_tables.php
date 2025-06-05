<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('rating_criteria', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('category')->nullable();
        $table->timestamps();
    });

    Schema::create('ratings', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reviewer_id')->nullable();
        $table->string('rater_name');
        $table->morphs('rateable');
        $table->integer('overall_rating');
        $table->text('comment')->nullable();
        $table->timestamps();

        $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
    });

    Schema::create('rating_values', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('rating_id');
        $table->unsignedBigInteger('rating_criteria_id');
        $table->integer('score');
        $table->timestamps();

        $table->foreign('rating_id')->references('id')->on('ratings')->onDelete('cascade');
        $table->foreign('rating_criteria_id')->references('id')->on('rating_criteria')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('rating_values');
    Schema::dropIfExists('ratings');
    Schema::dropIfExists('rating_criteria');
}



};
