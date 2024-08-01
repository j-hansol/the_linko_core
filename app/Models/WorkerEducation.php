<?php

namespace App\Models;

use App\Traits\Common\FindByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerEducation extends Model {
    use HasFactory, FindByUser;

    protected $fillable = [
        'user_id', 'write_user_id', 'degree', 'school_name', 'course_name', 'start_year', 'end_year',
        'origin_name', 'file_path'
    ];

    /**
     * 파일을 저장하고 그 경로를 리턴한다.
     * @param UploadedFile $file
     * @return string
     */
    public static function fileSave(UploadedFile $file) : string {
        return $file->store('worker_education', 'local');
    }

    /**
     * 삭제시 관련 파일을 삭제한다.
     * @return bool|null
     */
    public function delete() {
        if($this->file_path) Storage::disk('local')->delete($this->file_path);
        return parent::delete();
    }

    /**
     * 파일 경로 저장 필드 및 저장 경로를 리턴한다.
     * @return array
     */
    public static function basePath() : array {
        return [
            'file_path' => 'worker_education'
        ];
    }
}
