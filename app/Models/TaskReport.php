<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReport extends Model {
    use HasFactory;

    protected $fillable = ['order_task_id', 'user_id', 'title', 'body'];
}
