<?php

namespace App\Lib;

use App\Http\QueryParams\ListQueryParam;
use Illuminate\Database\Eloquent\Collection;

class PageResult {
    public int $code;
    public int $total = 0;
    public int $total_page = 0;
    public Collection $collection;
    private bool $has_total = false;

    public function __construct(PageCollection|Collection $collection, public ListQueryParam $param) {
        if($collection instanceof Collection) {
            $this->collection = $collection;
        }
        else {
            $this->total = $collection->total;
            $this->total_page = $collection->total_page;
            $this->collection = $collection->collection;
            $this->has_total = true;
        }
    }

    /**
     * 총 개수를 가지고 있는지 여부를 리턴한다.
     * @return bool
     */
    public function hasTotal() : bool {return $this->has_total;}
}
