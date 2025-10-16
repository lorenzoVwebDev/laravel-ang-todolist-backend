<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    /** @use HasFactory<\Database\Factories\UsersModelFactory> */
    use HasFactory;
}
