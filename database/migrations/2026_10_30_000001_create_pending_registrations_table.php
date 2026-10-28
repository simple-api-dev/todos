<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Registering with the developer's own email waits here for its emailed code, so nothing is created
     * (and the address cannot be squatted) until the mailbox owner confirms.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('integration_id');
            $table->string('email', 191);
            $table->string('name');
            $table->string('password_hash');
            $table->string('code_hash');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['integration_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_registrations');
    }
}
