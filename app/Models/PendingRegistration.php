<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A registration with the developer's own email address that is waiting for its emailed code.
 */
class PendingRegistration extends Model
{
    protected $fillable = ['integration_id', 'email', 'name', 'password_hash', 'code_hash', 'expires_at', 'attempts', 'sent_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
