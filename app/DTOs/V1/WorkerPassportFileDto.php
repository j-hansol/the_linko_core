<?php

namespace App\DTOs\V1;

use App\Models\WorkerPassport;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerPassportFileDto {
    private ?string $file_path = null;

    // 생성저
    function __construct(private readonly UploadedFile $file) {
        $this->file_path = WorkerPassport::saveFile($this->file);
    }

    // Getter
    public function getFilePath() : ?string {return $this->file_path;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerPassportFileDto
     * @throws HttpErrorsException
     */
    public static function createFromRequest(Request $request) : WorkerPassportFileDto {
        $file = $request->file('file');
        if(!$file)
            throw HttpErrorsException::getInstance([__('errors.file.required_file')], 400);
        return new static($file);
    }

    /**
     * 처리과정에 생성된 파일을 삭제한다.
     * @return void
     */
    public function rollback() : void {
        if($this->file_path) Storage::disk('local')->delete($this->file_path);
    }
}
