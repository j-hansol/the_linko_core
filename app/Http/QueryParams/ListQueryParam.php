<?php

namespace App\Http\QueryParams;

use Illuminate\Foundation\Application;
use OpenApi\Annotations as OA;

class ListQueryParam {
    public readonly ?string $field;
    public readonly ?string $operator;
    public readonly ?string $keyword;

    public readonly string $order;
    public readonly string $direction;

    public readonly int $page;
    public readonly int $page_per_items;
    public readonly int $start_rec_no;

    /**
     * @param Application $app
     * @OA\Parameter (
     *     name="filter",
     *     in="query",
     *     required=false,
     *     description="필터 대상 필드명",
     *     @OA\Schema (type="string")
     * )
     * @OA\Parameter (
     *     name="op",
     *     in="query",
     *     required=false,
     *     description="필터 연산자, 기본값 : like",
     *     @OA\Schema (type="string", enum={"like","=",">=","<=",">","<","<>"})
     * )
     * @OA\Parameter (
     *     name="keyword",
     *     in="query",
     *     required=false,
     *     description="필터 키워드",
     *     @OA\Schema (type="string")
     * )
     * @OA\Parameter (
     *     name="page",
     *     in="query",
     *     required=false,
     *     description="요청 페이지 번호, 기본값 : 1",
     *     @OA\Schema (type="integer")
     * )
     * @OA\Parameter (
     *     name="page_per_items",
     *     in="query",
     *     required=false,
     *     description="요청 페이지당 최대 항목 수, 기본값 : 50",
     *     @OA\Schema (type="integer")
     * )
     * @OA\Parameter (
     *     name="order",
     *     in="query",
     *     required=false,
     *     description="정렬 요청 필드",
     *     @OA\Schema (type="string")
     * )
     * @OA\Parameter (
     *     name="dir",
     *     in="query",
     *     required=false,
     *     description="정렬 방식, (asc, desc), 기본값 : asc",
     *     @OA\Schema (type="string")
     * )
     */
    public function __construct(Application $app) {
        list($this->field, $this->operator, $this->keyword) = get_filter($app->make('request'));
        list($this->order, $this->direction) = get_order($app->make('request'), 'id');
        list($this->page, $this->page_per_items, $this->start_rec_no) = get_page($app->make('request'));
    }
}
