<?php

namespace App\DTOs\V1;

use App\Lib\ContractFileGroup;
use App\Models\ContractFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ContractFileDto {
    // 속성
    private ?string $origin_name;
    private ?string $file_path;

    // Setter, Getter
    public function getTitle() : string {return $this->title;}
    public function getFileGroup() : ContractFileGroup {return ContractFileGroup::tryFrom($this->file_group);}
    public function setOriginName(UploadedFile $file) : void {$this->origin_name = $file->getClientOriginalName();}
    public function getOriginName() : ?string {return $this->origin_name;}
    public function setFilePath(UploadedFile $file) : void {$this->file_path = ContractFile::saveFile($file);}
    public function getFilePath() : ?string {return $this->file_path;}

    // Creator
    function __construct(
        private readonly string $title,
        private readonly ContractFileGroup $file_group
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return ContractFileDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : ContractFileDto {
        $dto = new static(
            $request->input('title'),
            ContractFileGroup::tryFrom($request->input('file_group'))
        );
        $file = $request->file('file');
        if($file) {
            $dto->setOriginName($file);
            $dto->setFilePath($file);
        }
        else throw new Exception('required file');
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'title' => $this->title,
            'file_group' => $this->file_group->value,
            'origin_name' => $this->origin_name,
            'file_path' => $this->file_path
        ];
    }
}
