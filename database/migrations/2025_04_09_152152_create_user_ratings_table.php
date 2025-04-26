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
            $table->integer('professional_capabilities')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('teamwork')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('ethics_and_relations')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('punctuality')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->integer('behavior')->check(function($query) {
                return $query->between(1, 5);
            });
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_ratings');
    }
}
