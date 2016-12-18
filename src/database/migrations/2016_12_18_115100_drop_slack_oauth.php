<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Warlof\Seat\Slackbot\Models\SlackUser;

class DropSlackOauth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('slack_users');
    }

    public function down()
    {
        Schema::create('slack_oauth', function (Blueprint $table) {
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('state')->nullable();
            $table->timestamps();

            $table->primary('client_id');
        });
    }
}
