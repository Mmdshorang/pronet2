<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->integer('overall_rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('rating_criteria_user_rating', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_rating_id');
            $table->unsignedBigInteger('rating_criteria_id');
            $table->integer('score');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_rating_id')->references('id')->on('user_ratings')->onDelete('cascade');
            $table->foreign('rating_criteria_id')->references('id')->on('rating_criterias')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rating_criteria_user_rating');
        Schema::dropIfExists('user_ratings');
    }
}
