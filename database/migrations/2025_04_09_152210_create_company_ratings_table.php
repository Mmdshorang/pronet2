<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('company_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->integer('overall_rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('rating_criteria_company_rating', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_rating_id');
            $table->unsignedBigInteger('rating_criteria_id');
            $table->integer('score');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('company_rating_id')->references('id')->on('company_ratings')->onDelete('cascade');
            $table->foreign('rating_criteria_id')->references('id')->on('rating_criterias')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rating_criteria_company_rating');
        Schema::dropIfExists('company_ratings');
    }
}
