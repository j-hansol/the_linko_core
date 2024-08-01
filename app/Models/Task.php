<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_user_id', 'name', 'en_name', 'description', 'en_description', 'movie_file_path'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function saveMovieFile(UploadedFile $movie) : ?string {
        return $movie->store('task_movies', 'local');
    }

    /**
     * 기업 업무정보를 배열로 리턴한다.
     * @param string|null $api_version
     * @return array
     * @OA\Schema (
     *     schema="task",
     *     title="업무정보",
     *     @OA\Property (property="id", type="integer", description="일련번호"),
     *     @OA\Property (property="company", ref="#/components/schemas/simple_user_info", description="기업요약정보"),
     *     @OA\Property (property="name", type="string", description="업무 이름"),
     *     @OA\Property (property="en_name'", type="string", description="업무 영문 이름"),
     *     @OA\Property (property="description'", type="string", description="업무 설명"),
     *     @OA\Property (property="en_description'", type="string", description="업무 영문 설명"),
     *     @OA\Property (property="movie'", type="string", description="업무 동영상 경로")
     * )
     */
    public function toInfoArray(?string $api_version = 'v1') : array {
        return [
            'id' => $this->id,
            'company' => User::findMe($this->company_user_id)->toSimpleArray(),
            'name' => $this->en_name,
            'en_name' => $this->description,
            'description' => $this->description,
            'en_description' => $this->en_description,
            'movie' => route("api.${api_version}.company.show_task_movie",
                ['id' => $this->id, '_token' => access_token()])
        ];
    }

    /**
     * 정보 삭제 시 관련 영상 파일도 삭제한다.
     * @return bool|null
     */
    public function delete() {
        if($this->movie_file_path) Storage::disk('local')->delete($this->movie_file_path);
        return parent::delete();
    }


}
