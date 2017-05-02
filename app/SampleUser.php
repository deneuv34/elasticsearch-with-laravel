<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleUser extends Model
{
    // Model of Sample User
    protected $table = 'users';
    protected $fillable = [
        'name', 'note',
    ];
}
