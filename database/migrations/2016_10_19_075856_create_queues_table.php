<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('username')->nullable();
            $table->integer('cycle')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('followed_at')->nullable();
            $table->dateTime('unfollowed_at')->nullable();
            $table->dateTime('last_check_at')->nullable();
            $table->dateTime('crawled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queues');
    }
}
