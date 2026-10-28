<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * Keys that use an external auth service get a local user the first time each external user is seen.
     * `external_id` is that user's id at the auth service. It is null for users who registered here.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('external_id', 191)->nullable();
            $table->unique(['integration_id', 'external_id']);
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
            $table->dropUnique(['integration_id', 'external_id']);
            $table->dropColumn('external_id');
        });
    }
}
