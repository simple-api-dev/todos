<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordResetToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_code_hash')->nullable();
            $table->timestamp('reset_expires_at')->nullable();
            $table->unsignedTinyInteger('reset_attempts')->default(0);
            $table->timestamp('reset_sent_at')->nullable();
            $table->index(['integration_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['integration_id', 'email']);
            $table->dropColumn(['reset_code_hash', 'reset_expires_at', 'reset_attempts', 'reset_sent_at']);
        });
    }
}
