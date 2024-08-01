<?php

namespace App\DTOs\V1;

use App\Models\WorkerExperience;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class WorkerExperienceDto {
    private ?string $company_address = null;
    private ?string $task = null;
    private ?string $part = null;
    private ?string $position = null;
    private ?string $job_description = null;
    private ?Carbon $end_date = null;
    private ?string $file_name = null;
    private ?string $file_path = null;

    // 생성자
    function __construct(
        private readonly string $company_name,
        private readonly Carbon $start_date
    ) {}

    // Setter, Getter
    public function getCompanyName() : string {return $this->company_name;}
    public function setCompanyAddress(?string $address) : void {$this->company_address = $address;}
    public function getCompanyAddress() : ?string {return $this->company_address;}
    public function setTask(?string $task) : void {$this->task = $task;}
    public function getTask() : ?string {return $this->task;}
    public function setPart(?string $part) : void {$this->part = $part;}
    public function getPart() : ?string {return $this->part;}
    public function setPosition(?string $position) : void {$this->position = $position;}
    public function getPosition() : ?string {return $this->position;}
    public function setJobDescription(?string $description) : void {$this->job_description = $description;}
    public function getJobDescription() : ?string {return $this->job_description;}
    public function getStartDate() : ?Carbon {return $this->start_date;}
    public function setEndDate(?Carbon $date) : void {$this->end_date = $date;}
    public function getEndDate() : ?Carbon {return $this->end_date;}
    public function setFile(UploadedFile $file) : void {
        $this->file_name = $file->getClientOriginalName();
        $this->file_path = WorkerExperience::fileSave($file);
    }
    public function getFileName() : ?string {return $this->file_name;}
    public function getFilePath() : ?string {return $this->file_path;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return $this
     */
    public static function createFromRequest(Request $request) : WorkerExperienceDto {
        $dto = new static(
            $request->input('company_name'),
            $request->date('start_date')
        );
        $dto->setCompanyAddress($request->input('company_address'));
        $dto->setTask($request->input('task'));
        $dto->setPart($request->input('part'));
        $dto->setPosition($request->input('position'));
        $dto->setJobDescription($request->input('job_description'));
        $dto->setEndDate($request->date('end_date'));
        if($request->hasFile('file')) $dto->setFile($request->file('file'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'task' => $this->task,
            'part' => $this->part,
            'position' => $this->position,
            'job_description' => $this->job_description,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'file_name' => $this->file_name,
            'file_path' => $this->file_path
        ];
    }

    public function toArrayForUpdate() : array {
        $array = [
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'task' => $this->task,
            'part' => $this->part,
            'position' => $this->position,
            'job_description' => $this->job_description,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
        ];
        if($this->file_path) {
            $array['file_name'] = $this->file_name;
            $array['file_path'] = $this->file_path;
        }

        return $array;
    }
}
