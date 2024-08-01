<?php

namespace App\DTOs\V1;

use App\Lib\EducationDegree;
use App\Models\WorkerEducation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class WorkerEducationDto {
    private ?string $course_name = null;
    private ?int $start_year = null;
    private ?int $end_year = null;
    private ?string $origin_name = null;
    private ?string $file_path = null;

    // 생성자
    function __construct(
        private readonly int $degree,
        private readonly string $school_name
    ) {}

    // Setter, Getter
    public function getDegree() : int {return $this->degree;}
    public function getSchoolName() : int {return $this->school_name;}
    public function setCourseName(?string $name) : void {$this->course_name = $name;}
    public function getCourseName() : ?string {return $this->course_name;}
    public function setStartYear(?int $year) : void {$this->start_year = $year;}
    public function getStartYear() : ?int {return $this->start_year;}
    public function setEndYear(?int $year) : void {$this->end_year = $year;}
    public function getEndYear() : ?int {return $this->end_year;}
    public function setFile(?UploadedFile $file) : void {
        if(!$file) return;

        $this->origin_name = $file->getClientOriginalName();
        $this->file_path = WorkerEducation::fileSave($file);
    }
    public function getOriginName() : ?string {return $this->origin_name;}
    public function getFilePath() : ?string {return $this->file_path;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return $this
     */
    public static function createFromRequest(Request $request) : WorkerEducationDto {
        $dto = new static(
            $request->enum('degree', EducationDegree::class)->value,
            $request->input('school_name')
        );
        $dto->setCourseName($request->input('course_name'));
        $dto->setStartYear($request->integer('start_year'));
        $dto->setEndYear($request->integer('end_year'));
        $dto->setFile($request->file('file'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'degree' => $this->degree,
            'school_name' => $this->school_name,
            'course_name' => $this->course_name,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'origin_name' => $this->origin_name,
            'file_path' => $this->file_path
        ];
    }
}
