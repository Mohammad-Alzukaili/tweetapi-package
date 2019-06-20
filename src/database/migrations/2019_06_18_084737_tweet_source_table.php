<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TweetSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void*
     */

    public function up()
    {
        Schema::create('tweet_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('screen_name');
            $table->json('user_data');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tweet_sources');
    }
}
