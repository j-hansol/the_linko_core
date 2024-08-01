<?php

namespace App\Models;

use App\Http\JsonResponses\Common\Data;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_user_id', 'recipient_user_id', 'mediation_user_id', 'sub_recipient_user_id', 'occupational_group_id',
        'type', 'uuid', 'title', 'body', 'sub_title', 'sub_body', 'worker_count', 'contract_date', 'sub_contract_date',
        'order_authentication', 'recipient_authentication', 'mediation_authentication', 'sub_recipient_authentication',
        'status'
    ];

    /**
     * 지정 ID의 계약정보를 가져온다.
     * @param int $id
     * @return Contract|null
     */
    public static function findMe(int $id) : ?Contract {
        return static::find($id);
    }

    /**
     * 계약정보를 배열로 리턴한다.
     * @param string|null $api_version
     * @return array
     * @OA\Schema(
     *     schema="contract_data",
     *     title="계약정보 데이터",
     *     @OA\Property (property="id", type="integer", description="계약정보 일련번호"),
     *     @OA\Property(property="order",description="발주자",ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="recipient",description="수주자",ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="sub_recipient",description="중계 수주자",ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="provider",description="근로자 공급자",ref="#/components/schemas/simple_user_info"),
     *     @OA\Property(property="occupational_group",description="대상 직업군",ref="#/components/schemas/occupational_group"),
     *     @OA\Property(property="type", description="계약 유형", type="integer"),
     *     @OA\Property(property="contract_group", description="계약 그룹(주/부)", type="integer"),
     *     @OA\Property(property="uuid", description="계약 식별 UUID", type="string"),
     *     @OA\Property(property="title", description="제목", type="string"),
     *     @OA\Property(property="sub_title", description="중계 계약 제목", type="string"),
     *     @OA\Property(property="body", description="계약 내용", type="string"),
     *     @OA\Property(property="sub_body", description="중계 계약 내용", type="string"),
     *     @OA\Property(property="worker_count", description="계약 근로자 수", type="integer"),
     *     @OA\Property(property="contract_date", description="계약일", type="date"),
     *     @OA\Property(property="sub_contract_date", description="중계 계약일", type="date"),
     *     @OA\Property(
     *          property="files",
     *          description="계약관련 파일",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/contract_file_info"
     *          )
     *     ),
     *     @OA\Property(property="status", description="계약 진행 상태", type="integer"),
     *     @OA\Property(property="edited", description="계약 내용 수정 여부", type="boolean"),
     *     @OA\Property (property="created_at", type="string", type="date-time", description="생성일시"),
     *     @OA\Property (property="updated_at", type="string", type="date-time", description="수정일시")
     * )
     */
    public function toInfoArray(?string $api_version = 'v1') : array {
        return [
            'id' => $this->id,
            'order' => User::findMe($this->order_user_id)?->toSimpleArray(),
            'recipient' => User::findMe($this->recipient_user_id)?->toSimpleArray(),
            'sub_recipient' => User::findMe($this->sub_recipient_user_id)?->toSimpleArray(),
            'provider' => User::findMe($this->provider_user_id)?->toSimpleArray(),
            'occupational_group' => OccupationalGroup::findMe($this->occupational_group_id)?->toInfoArray(),
            'parent' => Contract::findMe($this->parent_contract_id)?->toInfoArray(),
            'type' => $this->type,
            'contract_group' => $this->c_group,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'body' => $this->body,
            'sub_title' => $this->sub_title,
            'sub_body' => $this->sub_body,
            'worker_count' => $this->worker_count,
            'contract_date' => $this->contract_date,
            'sub_contract_date' => $this->sub_contract_date,
            'files' => $this->toArrayFileInfo(),
            'status' => $this->status,
            'edited' => $this->isEdited(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * 계약 정보의 일부 내용을 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="simple_contract_data",
     *     title="계약 요약 정보",
     *     @OA\Property (property="id", type="integer", description="계약정보 일련번호"),
     *     @OA\Property (property="order",description="발주자",ref="#/components/schemas/simple_user_info"),
     *     @OA\Property (property="title", description="제목", type="string"),
     *     @OA\Property (property="contract_date", description="계약일", type="date"),
     *     @OA\Property (property="status", description="계약 진행 상태", type="integer"),
     *     @OA\Property (property="created_at", type="string", type="date-time", description="생성일시"),
     *     @OA\Property (property="updated_at", type="string", type="date-time", description="수정일시")
     * )
     */
    public function toSimpleInfoArray() : array {
        return [
            'id' => $this->id,
            'order' => User::findMe($this->order_user_id)?->toSimpleArray(),
            'title' => $this->title,
            'contract_date' => $this->contract_date,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * 계약관련 파일 목록을 배열로 리턴한다.
     * @param string|null $api_version
     * @return array
     */
    private function toArrayFileInfo(?string $api_version = 'v1') : array {
        $files = $this->hasMany(ContractFile::class, 'contract_id')->get();
        $t = [];
        foreach($files as $file) $t[] = $file->toInfoArray($api_version);
        return $t;
    }

    /**
     * 계약정보 삭제 전에 계약정보와 연관된 파일을 삭제한다.
     * @return bool|null
     */
    public function delete() : ?bool {
        $files = $this->hasMany(ContractFile::class, 'contract_id')->get();
        foreach($files as $file) $file->delete();
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    /**
     * 계약 정보를 응답한다.
     * @param string $api_version
     * @return JsonResponse
     */
    public function response(string $api_version = 'v1') : JsonResponse {
        return new Data($this->toInfoArray($api_version));
    }
}
