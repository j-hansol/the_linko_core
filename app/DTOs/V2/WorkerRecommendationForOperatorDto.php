<?php

namespace App\DTOs\V2;

use App\Lib\ExcludeItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkerRecommendationForOperatorDto {
    private ?int $worker_count = 0;
    private ?array $provided_models = [];
    private ?array $excluded_informations = [];
    private ?array $target_user_ids = [];

    // 생성자
    function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly int $occupational_group_id,
        private readonly Carbon $expire_date,
        private readonly int $active
    ) {}

    // Getter, Setter
    public function getTitle() : string {return $this->title;}
    public function getBody() : string {return $this->body;}
    public function setWorkerCount(?int $count) : void {$this->worker_count = $count;}
    public function getWorkerCount() : int {return $this->worker_count;}
    public function getOccupationalGroupId() : int {return $this->occupational_group_id;}
    public function setProvidedModels(array $models = []) : void {
        $aliases = array_flip(config('worker_recommendation.model_alias'));
        foreach($models as $m) $this->provided_models[] = $aliases[trim($m)];
    }
    public function getProvidedModels() : array {return $this->provided_models;}
    public function setExcludeInformations(array $infos = []) : void {
        foreach($infos as $i) $this->excluded_informations[] = trim($i);
    }
    public function getExcludeInformations() : array {return $this->excluded_informations;}
    public function setTargetUserIds(array $ids) : void {
        if(empty($ids)) return;
        $this->target_user_ids = $ids;
    }
    public function getTargetUserIds() : array {return $this->target_user_ids;}
    public function setTargetUserIdAliases(array $aliases) : void {
        if(empty($aliases)) return;
        $ids = User::query()
            ->whereIn('id_alias', $aliases)
            ->get()->pluck('id')->toArray();
        $this->target_user_ids = $ids;
    }
    public function getExpireDate() : Carbon {return $this->expire_date;}
    public function getActive() : int {return $this->active;}

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerRecommendationForOperatorDto
     */
    public static function createFromRequest(Request $request) : WorkerRecommendationForOperatorDto {
        $dto = new static(
            $request->input('title'),
            $request->input('body'),
            $request->integer('occupational_group_id'),
            $request->date('expire_date'),
            $request->boolean('active')
        );
        $dto->setWorkerCount($request->integer('worker_count', 0));
        $dto->setProvidedModels(explode(',', $request->input('provided_models')));
        $dto->setExcludeInformations(explode(',', $request->input('exclude_items')));
        $dto->setTargetUserIds(explode(',', $request->input('target_user_ids')));
        $dto->setTargetUserIdAliases(explode(',', $request->input('target_user_id_aliases')));
        return $dto;
    }

    // for model
    public function toRequestArray() : array {
        return [
            'occupational_group_id' => $this->occupational_group_id,
            'title' => $this->title,
            'body' => $this->body,
            'worker_count' => $this->worker_count
        ];
    }

    public function toRecommendationArray() : array {
        return [
            'provided_models' => json_encode($this->provided_models),
            'excluded_informations' => json_encode($this->excluded_informations),
            'expire_date' => $this->expire_date->format('Y-m-d'),
            'active' => $this->active
        ];
    }
}
