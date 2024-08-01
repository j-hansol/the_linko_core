<?php

namespace App\DTOs\V1;

use App\Models\VisaDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class VisaDocumentDto {
    // 속성
    private ?string $file_path;
    private ?string $origin_name;
    private bool $delete_prev_file = false;
    // Setter, Getter
    public function getTitle() : string {return $this->title;}
    public function getTypeId() : int {return $this->type_id;}
    public function setFile(?UploadedFile $file) : void {
        if($file) {
            $this->file_path = VisaDocument::saveFile($file);
            $this->origin_name = $file->getClientOriginalName();
        }
    }
    public function getOriginName() : ?string {return $this->origin_name;}
    public function getFile() : ?string {return $this->file_path;}
    public function setDeletePrevFile(bool $delete) : void {$this->delete_prev_file = $delete;}
    public function getDeletePrevFile() : bool {return $this->delete_prev_file;}

    // Creator
    function __construct(
        private readonly int $type_id,
        private readonly string $title,
    ) {}

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaDocumentDto
     */
    public static function createFromRequest(Request $request) : VisaDocumentDto {
        $dto = new static(
            $request->integer('type_id'),
            $request->input('title'),
        );
        $dto->setFile($request->file('file_path'));
        $dto->setDeletePrevFile($request->boolean('delete_prev_file'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'title' => $this->title,
            'type_id' => $this->type_id,
            'file_path' => $this->file_path,
            'origin_name' => $this->origin_name
        ];
    }
}
