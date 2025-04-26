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
            $table->integer('salary_timeliness')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('benefits')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('work_environment')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('management')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_ratings');
    }
}
