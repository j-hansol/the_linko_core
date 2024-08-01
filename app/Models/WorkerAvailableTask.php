<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerAvailableTask extends Model {
    use HasFactory;

    protected $table = [
        'user_id', 'name', 'description', 'file_name', 'file_path', 'movie_name', 'movie_path', 'movie_link', 'link'];

}
