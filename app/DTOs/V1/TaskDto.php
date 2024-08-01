<?php

namespace App\DTOs\V1;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class TaskDto {
    // 속성
    private ?string $description;
    private ?string $en_description;
    private ?string $movie_file_path;
    private bool $delete_prev_movie = false;

    // 생성자
    function __construct(private readonly string $name, private readonly string $en_name) {}

    // Setter, Getter
    public function getName() : string {return $this->name;}
    public function setDescription(?string $description) : void {$this->description = $description;}
    public function getDescription() : ?string {return $this->description;}
    public function getEnName() : string {return $this->en_name;}
    public function setEnDescription(?string $description) : void {$this->en_description = $description;}
    public function getEnDescription() : ?string {return $this->en_description;}
    public function setMovieFile(UploadedFile $file) : void {$this->movie_file_path = Task::saveMovieFile($file);}
    public function getMovieFile() : ?string {return $this->movie_file_path;}
    public function setDeletePrevMovie(bool $b) : void {$this->delete_prev_movie = $b;}
    public function getDeletePrevMovie() : bool {return $this->delete_prev_movie;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return TaskDto
     */
    public static function createFromRequest(Request $request) : TaskDto {
        $dto = new static(
            $request->input('name'),
            $request->input('en_name')
        );
        $dto->setDescription($request->input('description'));
        $dto->setEnDescription($request->input('en_description'));
        if($request->hasFile('movie')) $dto->setMovieFile($request->file('movie'));
        if($request->has('delete_prev_movie')) $dto->setDeletePrevMovie($request->boolean('delete_prev_movie'));
        return $dto;
    }

    // for Model
    public function toArray() : array {
        $t = [
            'name' => $this->name,
            'en_name' => $this->en_name,
            'description' => $this->description,
            'en_description' => $this->en_description,
        ];
        if($this->delete_prev_movie) $t['movie_file_path'] = $this->movie_file_path;
        return $t;
    }
}
