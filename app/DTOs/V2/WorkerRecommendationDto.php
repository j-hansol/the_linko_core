<?php

namespace App\DTOs\V2;

use App\Lib\ExcludeItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkerRecommendationDto {
    // 속성
    private ?array $provided_models = [];
    private ?array $excluded_informations = [];
    private ?array $target_user_ids = [];

    function __construct(
        private readonly Carbon $expire_date,
        private readonly int $active
    ) {}

    public function setProvidedModels(array $models = []) : void {
        $aliases = array_flip(config('worker_recommendation.model_alias'));
        foreach($models as $m) $this->provided_models[] = $aliases[trim($m)];
    }
    public function getProvidedModels() : array {return $this->provided_models;}
    public function setExcludeInformations(array $infos = []) : void {
        foreach($infos as $i) $this->excluded_informations[] = trim($i);
    }
    public function getExcludeInformations() : array {return $this->excluded_informations;}
    public function getExpireDate() : Carbon {return $this->expire_date;}
    public function setTargetUserIds(array $ids) : void {
        foreach($ids as $id) $this->target_user_ids[] = (int)$id;
    }
    public function getTargetUserIds() : array {return $this->target_user_ids;}
    public function setTargetUserIdAliases(array $aliases) : void {
        if(empty($aliases)) return;
        $ids = User::query()
            ->whereIn('id_alias', $aliases)
            ->get()->pluck('id')->toArray();
        $this->target_user_ids = $ids;
    }
    public function getActive() : int {return $this->active;}

    /**
     * 요청 데이터로부터 DTO 객체를 리턴한다.
     * @param Request $request
     * @return WorkerRecommendationDto
     */
    public static function createFromRequest(Request $request) : WorkerRecommendationDto {
        $dto = new static(
            $request->date('expire_date'),
            $request->boolean('active')
        );
        $dto->setProvidedModels(explode(',', $request->input('provided_models')));
        $dto->setExcludeInformations(explode(',', $request->input('exclude_items')));
        $dto->setTargetUserIds(explode(',', $request->input('target_user_ids')));
        $dto->setTargetUserIdAliases(explode(',', $request->input('target_user_id_aliases')));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'provided_models' => json_encode($this->provided_models),
            'excluded_informations' => json_encode($this->excluded_informations),
            'expire_date' => $this->expire_date->format('Y-m-d'),
            'active' => $this->active
        ];
    }
}
