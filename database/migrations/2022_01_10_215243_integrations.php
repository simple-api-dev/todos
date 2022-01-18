<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Integrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('apikey')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('code')->nullable();
            $table->string('status')->default('ACCT_CREATED'); //ACCT_CREATED, MAIL_SENT, KEY_USED
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integrations');
    }
}
