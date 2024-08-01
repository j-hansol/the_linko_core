<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReportPhoto extends Model {
    use HasFactory;

    protected $fillable = ['task_report_id', 'file_path'];
}
