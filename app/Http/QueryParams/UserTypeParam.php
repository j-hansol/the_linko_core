<?php

namespace App\Http\QueryParams;

use App\Lib\MemberType;
use Illuminate\Foundation\Application;
use OpenApi\Annotations as OA;

class UserTypeParam {
    public ?MemberType $type;

    /**
     * @param Application $app
     * @OA\Parameter (
     *     name="type",
     *     in="query",
     *     required=true,
     *     description="회원 유형",
     *     @OA\Schema (ref="#/components/schemas/MemberType")
     * )
     */
    function __construct(Application $app) {
        $this->type = MemberType::tryFrom($app->make('request')->get('type'));
    }
}
