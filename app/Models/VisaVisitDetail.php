<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

class VisaVisitDetail extends Model
{
    use HasFactory;

    protected $table = 'visa_vist_details';

    protected $fillable = [
        'visa_application_id', 'user_id', 'purpose', 'other_purpose_detail', 'intended_stay_period',
        'intended_entry_date', 'text_intended_entry_date', 'address_in_korea', 'contact_in_korea', 'visit_korea_ids', 'visit_country_ids',
        'stay_family_ids', 'family_member_ids'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자발급 시 사용한 방문상세정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaVisitDetail|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaVisitDetail {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 방문 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="visa_visit_detail",
     *     title="방문상세정보",
     *     @OA\Property (
     *          property="purpose",
     *          type="integer",
     *          description="방문목적 구분",
     *     ),
     *     @OA\Property (
     *          property="other_purpose_detail",
     *          type="string",
     *          description="기타 방문의 경우 설명",
     *     ),
     *     @OA\Property (
     *          property="intended_stay_period",
     *          type="integer",
     *          description="체류기간",
     *     ),
     *     @OA\Property (
     *          property="intended_entry_date",
     *          type="string",
     *          format="date",
     *          description="입국예정일자",
     *     ),
     *     @OA\Property (
     *          property="address_in_korea",
     *          type="string",
     *          description="체류 국내 주소",
     *     ),
     *     @OA\Property (
     *          property="contact_in_korea",
     *          type="string",
     *          description="체류 국내 전화번호",
     *     ),
     *     @OA\Property (
     *          property="visit_list",
     *          type="array",
     *          description="최근 5년 이내 한국 방문 내역",
     *          @OA\Items (
     *              type="object",
     *              ref="#/components/schemas/worker_visited_korea"
     *          )
     *     ),
     *     @OA\Property (
     *          property="visit_countries",
     *          type="array",
     *          description="최근 5년 이내 방문 국가 내역",
     *          @OA\Items (
     *              type="object",
     *              ref="#/components/schemas/worker_visited_country"
     *          )
     *     ),
     *     @OA\Property (
     *          property="stay_family",
     *          type="array",
     *          description="국내 거주 가족",
     *          @OA\Items (
     *              type="object",
     *              ref="#/components/schemas/worker_family"
     *          )
     *     ),
     *     @OA\Property (
     *          property="family_member",
     *          type="array",
     *          description="동반 입국 가족",
     *          @OA\Items (
     *              type="object",
     *              ref="#/components/schemas/worker_family"
     *          )
     *     )
     * )
     */
    public function toInfoArray() : array {
        return [
            'purpose' => $this->purpose,
            'other_purpose_detail' => $this->other_purpose_detail,
            'intended_stay_period' => $this->intended_stay_period,
            'intended_entry_date' => $this->intended_entry_date,
            'address_in_korea' => $this->address_in_korea,
            'contact_in_korea' => $this->contact_in_korea,
            'visit_list' => $this->_getVisitCountryList($this->visit_korea_ids),
            'visit_countries' => $this->_getVisitCountryList($this->visit_country_ids, true),
            'stay_family' => $this->_getFamilies($this->stay_family_ids),
            'family_member' => $this->_getFamilies($this->family_member_ids)
        ];
    }

    /**
     * 최근 5년 이내 방문 내역을 배열로 리턴한다.
     * @param string|null $list
     * @param $include_country
     * @return array
     */
    private function _getVisitCountryList(?string $list, $include_country = false) : array {
        if(!$list) return [];
        $ids = json_decode($list);
        if(!$ids) return [];
        $ret = [];
        foreach($ids as $id) {
            $ret[] = WorkerVisit::findMe($id)?->toInfoArray($include_country);
        }
        return $ret;
    }

    /**
     * 가족정보를 배열로 리턴한다.
     * @param string|null $list
     * @return array
     */
    private function _getFamilies(?string $list) : array {
        if(!$list) return [];
        $ids = json_decode($list);
        $ret = [];
        foreach($ids as $id) $ret[] = WorkerFamily::findMe($id)?->toInfoArray();
        return $ret;
    }

    public function delete() {
        $this->_unLinkWorkerVisit($this->visit_korea_ids);
        $this->_unLinkWorkerVisit($this->visit_country_ids);
        $this->_unlinkWorkerFamily($this->stay_family_ids);
        $this->_unlinkWorkerFamily($this->family_member_ids);
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    /**
     * 지정 필드의 내용을 방문정보 참조에 반영한다.
     * @param $field_name
     * @return void
     */
    private function _syncVisit($field_name) : void {
        $origin = json_decode($this->getOriginal($field_name)) ?? [];
        $new = json_decode($this->getAttribute($field_name)) ?? [];
        $no_action_target = array_intersect($origin, $new);
        $unlink_target = array_diff($origin, $no_action_target);
        $link_target = array_diff($new, $no_action_target);
        if($unlink_target) {
            foreach($unlink_target as $id) {
                $t = WorkerVisit::findMe($id);
                $t?->unReference();
            }
        }
        if($link_target) {
            foreach ($link_target as $id) {
                $t = WorkerVisit::findMe($id);
                $t?->reference();
            }
        }
    }

    /**
     * 지정 필드의 내용을 가족정보 참조에 반영한다.
     * @param $field_name
     * @return void
     */
    private function _syncFamily($field_name) : void {
        $origin = json_decode($this->getOriginal($field_name)) ?? [];
        $new = json_decode($this->getAttribute($field_name)) ?? [];
        $no_action_target = array_intersect($origin, $new);
        $unlink_target = array_diff($origin, $no_action_target);
        $link_target = array_diff($new, $no_action_target);
        if($unlink_target) {
            foreach($unlink_target as $id) {
                $t = WorkerFamily::findMe($id);
                $t?->unReference();
            }
        }
        if($link_target) {
            foreach ($link_target as $id) {
                $t = WorkerFamily::findMe($id);
                $t?->reference();
            }
        }
    }

    /**
     * 방문국가 정보와의 참조를 해제한다.
     * @param $json_str
     * @return void
     */
    private function _unLinkWorkerVisit($json_str) : void {
        $data = json_decode($json_str);
        if(is_array($data)) {
            foreach($data as $id) {
                $t = WorkerVisit::findMe($id);
                $t?->unReference();
            }
        }
    }

    /**
     * 방문가족 정보와의 참조를 해제한다.
     * @param $json_str
     * @return void
     */
    private function _unlinkWorkerFamily($json_str) : void {
        $data = json_decode($json_str);
        if(is_array($data)) {
            foreach($data as $id) {
                $t = WorkerFamily::findMe($id);
                $t?->unReference();
            }
        }
    }

    /**
     * 방문정보 및 가족정보 참조를 동기화한다.
     * @param array $options
     * @return bool
     */
    public function save(array $options = []) {
        $this->_syncVisit('visit_korea_ids');
        $this->_syncVisit('visit_country_ids');
        $this->_syncFamily('stay_family_ids');
        $this->_syncFamily('family_member_ids');
        return parent::save($options); // TODO: Change the autogenerated stub
    }
}
