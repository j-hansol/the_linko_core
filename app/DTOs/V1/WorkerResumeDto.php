<?php

namespace App\DTOs\V1;

use App\Models\WorkerResume;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerResumeDto {
    private ?string $file_name = null;
    private ?string $file_path = null;

    // 생성자
    function  __construct(UploadedFile $file) {
        $this->file_name = $file->getClientOriginalName();
        $this->file_path = WorkerResume::saveFile($file);
    }

    // Getter
    public function getFileName() : string {return $this->file_name;}
    public function getFilePath() : string {return $this->file_path;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerResumeDto
     */
    public static function createFromRequest(Request $request) : WorkerResumeDto {
        return new static($request->file('file'));
    }

    // for model
    public function toArray() : array {
        return [
            'file_name' => $this->file_name,
            'file_path' => $this->file_path
        ];
    }

    /**
     * 처리과정에 생성된 파일을 삭제한다.
     * @return void
     */
    public function rollback() : void {
        if($this->file_path) Storage::disk('local')->delete($this->file_path);
    }
}
