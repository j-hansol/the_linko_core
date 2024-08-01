<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Lib\BodyType;
use App\Lib\ExcludeItem;
use App\Models\RecommendedWorker;
use App\Models\User;
use App\Models\WorkerBodyPhoto;
use App\Models\WorkerEducation;
use App\Models\WorkerExperience;
use App\Models\WorkerInfo;
use App\Models\WorkerRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

class RecommendedWorkerInfo extends JsonResponse {
    function __construct(RecommendedWorker $worker, WorkerRecommendation $recommendation) {
        parent::__construct(static::toArray($worker, $recommendation));
    }

    /**
     * 근로자 공유징버롤 배열로 리턴한다.
     * @param RecommendedWorker $worker
     * @param WorkerRecommendation $recommendation
     * @return array
     * @OA\Schema(
     *     schema="recommended_worker_info",
     *     title="추천 근로자 정보 (제외 정보 미포함)",
     *     @OA\Property(property="id", type="integer", description="일련번호"),
     *     @OA\Property(property="user", ref="#/components/schemas/user_info"),
     *     @OA\Property(property="worker_info", ref="#/components/schemas/worker_info"),
     *     @OA\Property(property="worker_educations", type="array", @OA\Items(ref="#/components/schemas/worker_education_info")),
     *     @OA\Property(property="worker_experiences", type="array", @OA\Items(ref="#/components/schemas/worker_experience_info")),
     *     @OA\Property(property="worker_face_photo", ref="#/components/schemas/worker_body_photo"),
     *     @OA\Property(property="status", type="integer", description="추천 근로자 상태")
     * )
     */
    public static function toArray(RecommendedWorker $worker, WorkerRecommendation $recommendation) : array {
        $models = json_decode($recommendation->provided_models);

        $info = [
            'id' => $worker->id,
            'user' => null,
            'worker_info' => null,
            'worker_educations' => [],
            'worker_experiences' => [],
            'worker_face_photo' => null,
            'status' => $worker->status
        ];
        $user = User::findMe($worker->worker_user_id);
        foreach($models as $model) {
            if($model == User::class) $info['user'] = static::_procUser($user, $recommendation);
            elseif($model == WorkerInfo::class) $info['worker_info'] = static::_procWorkerInfo($user, $recommendation);
            elseif($model == WorkerEducation::class) $info['worker_educations'] = static::_procWWorkerEducation($user, $recommendation);
            elseif($model == WorkerExperience::class) $info['worker_experiences'] = static::_procWorkerExperiences($user, $recommendation);
            elseif($model == WorkerBodyPhoto::class) $info['worker_face_photo'] = static::_procWorkerPhoto($user, $recommendation);
        }
        return $info;
    }

    /**
     * 사용자 계정 정보를 가공한다.
     * @param User $user
     * @param WorkerRecommendation $recommendation
     * @return array
     */
    private static function _procUser(User $user, WorkerRecommendation $recommendation) : ?array {
        $info = UserInfoResponse::toArray($user);
        $exclude = json_decode($recommendation->excluded_informations);
        $default_exclude = [
            'old_family_name', 'old_given_names', 'registration_no', 'boss_name', 'manager_name', 'telephone',
            'fax', 'road_map', 'longitude', 'latitude', 'organization'
        ];
        foreach($default_exclude as $e) if(isset($info[$e])) unset($info[$e]);

        if($exclude) {
            if(in_array(ExcludeItem::EMAIL->name, $exclude)) unset($info['email']);
            if(in_array(ExcludeItem::ADDRESS->name, $exclude)) unset($info['address']);
            if(in_array(ExcludeItem::BIRTHDAY->name, $exclude)) unset($info['birthday']);
            if(in_array(ExcludeItem::COUNTRY->name, $exclude)) {
                unset($info['country']);
                unset($info['birth_country']);
                unset($info['another_nationalities']);
            }
            if(in_array(ExcludeItem::GENDER->name, $exclude)) unset($info['sex']);
            if(in_array(ExcludeItem::IDENTIFIER_NO->name, $exclude)) unset($info['identity_no']);
            if(in_array(ExcludeItem::PERSON_NAME->name, $exclude)) {
                unset($info['name']);
                unset($info['family_name']);
                unset($info['given_names']);
                unset($info['hanja_name']);
            }
            if(in_array(ExcludeItem::PHONE_NUMBER->name, $exclude)) unset($info['cell_phone']);
        }

        return !empty($info) ? $info : null;
    }

    /**
     * 근로자 정보를 가공한다.
     * @param User $user
     * @param WorkerRecommendation $recommendation
     * @return array
     */
    private static function _procWorkerInfo(User $user, WorkerRecommendation $recommendation) : ?array {
        $info = WorkerInfoResponse::toArray(WorkerInfo::findByUser($user));
        if(!$info) return null;

        unset($info['user_info']);
        $exclude = json_decode($recommendation->excluded_informations);
        if(in_array(ExcludeItem::ADDRESS->name, $exclude)) unset($info['current_address']);
        if(in_array(ExcludeItem::SPOUSE_NAME->name, $exclude)) unset($info['spouse']);
        if(in_array(ExcludeItem::CHILDREN_NAME->name, $info)) unset($info['children_names']);

        return !empty($info) ? $info : null;
    }

    /**
     * 근로자 학력정보를 처리하여 배열로 리턴한다.
     * @param User $user
     * @param WorkerRecommendation $recommendation
     * @return array|null
     */
    private static function _procWWorkerEducation(User $user, WorkerRecommendation $recommendation) : ?array {
        $education = $user->getEducation();
        $exclude = json_decode($recommendation->excluded_informations);

        $default_exclude = ['user', 'writer'];
        foreach($default_exclude as $e) if(isset($info[$e])) unset($info[$e]);

        $info = [];
        foreach($education as $e) {
            $a_edu = WorkerEducationInfo::toArray($e);
            if(in_array(ExcludeItem::CERTIFICATION_FILE->name, $exclude)) {
                unset($a_edu['origin_name']);
                unset($a_edu['is_image']);
                unset($a_edu['file_url']);
            }
            $info[] = $a_edu;
        }

        return !empty($info) ? $info : null;
    }

    /**
     * 근로자의 경력정보를 처리하여 배열로 리턴한다.
     * @param User $user
     * @param WorkerRecommendation $recommendation
     * @return array|null
     */
    private static function _procWorkerExperiences(User $user, WorkerRecommendation $recommendation) : ?array {
        $experiences = $user->getExperiences();
        $exclude = json_decode($recommendation->excluded_informations);

        $default_exclude = ['user', 'writer'];
        foreach($default_exclude as $e) if(isset($info[$e])) unset($info[$e]);

        $info = [];
        foreach($experiences as $e) {
            $a_expr = WorkerExperienceInfo::toArray($e);
            if($a_expr['start_date']) {
                $a_expr['start_date'] = Carbon::createFromFormat('Y-m-d', $a_expr['start_date'])->format('Y-m');
            }
            if($a_expr['end_date']) {
                $a_expr['end_date'] = Carbon::createFromFormat('Y-m-d', $a_expr['end_date'])->format('Y-m');
            }
            if(in_array(ExcludeItem::ADDRESS->name, $exclude)) unset($a_expr['company_address']);
            if(in_array(ExcludeItem::CERTIFICATION_FILE->name, $exclude)) {
                unset($a_expr['origin_name']);
                unset($a_expr['is_image']);
                unset($a_expr['file_url']);
            }
            $info[] = $a_expr;
        }

        return !empty($info) ? $info : null;
    }

    /**
     * 근로자의 얼굴 사진을 처리항 배열로 리턴한다.
     * @param User $user
     * @param WorkerRecommendation $recommendation
     * @return array|null
     */
    private static function _procWorkerPhoto(User $user, WorkerRecommendation $recommendation) : ?array {
        if(!in_array(ExcludeItem::PHOTO->name, json_decode($recommendation->excluded_informations))) {
            $face = WorkerBodyPhoto::query()
                ->where('user_id', $user->id)
                ->where('type', BodyType::FACE->value)
                ->get()->first();
            if($face) {
                $a_info = WorkerPhoto::toArray($face);
                if(isset($a_info['file_url'])) return ['file_url' => $a_info['file_url']];
            }
        }

        return null;
    }
}
