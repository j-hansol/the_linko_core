<?php

namespace App\Http\QueryParams;

use App\Lib\EvalTarget;
use Illuminate\Foundation\Application;

class EvalTargetQueryParam {
    public readonly EvalTarget $target;
    public readonly bool $active;

    /**
     * @param Application $app
     * @OA\Parameter(
     *     name="target",
     *     in="query",
     *     description="평가대상",
     *     required=true,
     *     @OA\Schema(ref="#/components/schemas/EvalTarget")
     * ),
     * @OA\Parameter(
     *     name="active",
     *     in="query",
     *     description="이용 가능 여부",
     *     required=true,
     *     @OA\Schema(type="integer",enum={"0","1"})
     * )
     */
    public function __construct(Application $app) {
        $request = $app->make('request');
        $this->target = $request->enum('target', EvalTarget::class);
        $this->active = $request->get('active') == 1;
    }
}
