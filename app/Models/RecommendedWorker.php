<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendedWorker extends Model {
    use HasFactory;

    protected $fillable = ['worker_recommendation_id', 'worker_user_id', 'status'];
    public $timestamps = false;
}
