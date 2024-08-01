<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerEtcExperienceFile extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'write_user_id', 'file_name', 'file_path'];

    /**
     * 파일을 저장하고 그 겨열로를 리턴한다.
     * @param UploadedFile $file
     * @return string
     */
    public static function saveFile(UploadedFile $file) : string {
        return $file->store('worker_etc_experience_files', 'local');
    }

    /**
     * 파일을 삭제한다.
     * @return bool|null
     */
    public function delete() {
        if($this->file_path) Storage::disk('local')->delete($this->file_path);
        return parent::delete();
    }
}
