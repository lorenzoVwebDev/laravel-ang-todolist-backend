<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasksModel extends Model
{
    /** @use HasFactory<\Database\Factories\TasksModelFactory> */
    use HasFactory;

    protected $table = 'tasks';
    public $timestamps = false;
}
