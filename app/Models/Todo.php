<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $integration_id)
 * @method static make(array $all)
 * @method static find($id)
 */
class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'description', 'completed', 'author', 'integration_id', 'meta'
    ];

    protected $hidden = [
        'updated_at', 'created_at', 'integration_id'
    ];

    public function getMetaAttribute($value){
       return $value ? json_decode($value) : null;
    }
}
