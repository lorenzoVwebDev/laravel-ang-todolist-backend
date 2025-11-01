<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    public $timestamps = false;
    /** @use HasFactory<\Database\Factories\UsersModelFactory> */
    use HasFactory;
}
