<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ClearUnverifiedAdmins extends Migration
{
    /**
     * Admin used to be granted to whoever registered with the developer's email, without proving they own it.
     * Existing admin flags are not trustworthy, so they are cleared: the developer becomes admin again by
     * resetting their password (users/forgot then users/reset), which proves they own the mailbox.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->update(['admin' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
