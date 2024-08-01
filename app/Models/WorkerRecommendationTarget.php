<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerRecommendationTarget extends Model {
    use HasFactory;

    protected $fillable = ['worker_recommendation_id', 'user_id'];
    public $timestamps = false;
}
