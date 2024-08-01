<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReportFile extends Model {
    use HasFactory;

    protected $fillable = ['task_report_id', 'origin_name', 'file_path'];
}
