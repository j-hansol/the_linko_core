<?php

namespace App\Lib;


use Illuminate\Database\Eloquent\Collection;

class PageCollection {
    function __construct(public int $total, public int $total_page, public Collection $collection) {}
}
