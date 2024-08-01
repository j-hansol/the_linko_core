<?php

namespace App\DTOs\V1;

use App\Models\WorkerVisaDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerVisaDocumentsDto {
    // 속성
    private array $file_paths = [];


    // Setter, Getter
    public function getTitle() : string {return $this->title;}
    public function getTypeId() : int {return $this->type_id;}
    public function setFiles(?array $files) : void {
        if(!$files || !is_array($files)) return;
        foreach($files as $file) {
            if($file instanceof UploadedFile) {
                $this->file_paths[] = [
                    'path' => WorkerVisaDocument::saveFile($file),
                    'origin' => $file->getClientOriginalName()
                ];
            }
        }
    }
    public function getFiles() : array {return $this->file_paths;}

    // Creator
    function __construct(
        private readonly int $type_id,
        private readonly string $title,
    ) {}

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerVisaDocumentsDto
     */
    public static function createFromRequest(Request $request) : WorkerVisaDocumentsDto {
        $dto = new static(
            $request->integer('type_id'),
            $request->input('title'),
        );
        $dto->setFiles($request->file('file_path'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        $ret = [];
        foreach ($this->file_paths as $file) {
            $ret[] = [
                'title' => $this->title,
                'type_id' => $this->type_id,
                'file_path' => $file['path'],
                'origin_name' => $file['origin']
            ];
        }
        return $ret;
    }

    /**
     * 모든 파일을 삭제한다.
     * @return void
     */
    public function deleteFiles() : void {
        foreach($this->file_paths as $path) Storage::disk('local')->delete($path);
    }

    /**
     * 처리과정에 생성된 파일을 삭제한다.
     * @return void
     */
    public function rollback() : void {
        $this->deleteFiles();
    }
}
