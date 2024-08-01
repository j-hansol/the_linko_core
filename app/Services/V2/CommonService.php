<?php

namespace App\Services\V2;

use App\Http\QueryParams\EvalTargetQueryParam;
use App\Http\QueryParams\ListQueryParam;
use App\Lib\PageCollection;
use App\Models\Country;
use App\Models\EvalInfo;
use App\Models\OccupationalGroup;
use App\Models\VisaApplication;
use App\Models\VisaDocument;
use App\Models\VisaDocumentType;
use App\Services\Common\HttpException;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class CommonService {
    /**
     * 서비스 프로바이더를 통해 인스턴스를 가져온다.
     * @return CommonService
     * @throws Exception
     */
    public static function getInstance() : CommonService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }

    /**
     * 국가정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listCountry(ListQueryParam $param) : PageCollection {
        $query = Country::orderBy($param->order, $param->direction)
            ->skip($param->start_rec_no)->take($param->page_per_items)
            ->where('active', 1)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 직업군정보 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return PageCollection
     * @throws HttpException
     */
    public function listOccupationalGroup(ListQueryParam $param) : PageCollection {
        $query = OccupationalGroup::orderBy($param->order, $param->direction)
            ->where('leaf_node', 1)
            ->skip($param->start_rec_no)->take($param->page_per_items)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 비자발급시 필요한 문서 유형 목록을 출력한다.
     * @param ListQueryParam $param
     * @param VisaApplication|null $visa
     * @return PageCollection
     */
    public function listVisaDocumentType(ListQueryParam $param, ?VisaApplication $visa = null) : PageCollection {
        $query = VisaDocumentType::orderBy('weight', 'desc')
            ->orderBy($param->order, $param->direction)
            ->skip($param->start_rec_no)->take($param->page_per_items)
            ->when($visa, function(Builder $query) use ($visa) {
                $documents = VisaDocument::findByVisa($visa);
                $types = [];
                foreach($documents as $d)
                    if($d instanceof VisaDocument && $d->isRequiredDocument()) $types[] = $d->type_id;
                if(!empty($types)) $query->whereNotIn('id', $types);
            })
            ->when(!$param->field, function(Builder $query){$query->where('active', 1);})
            ->when($param->field == 'active', function(Builder $query) use($param) {
                $query->where('active', boolval($param->keyword) ? 1 : 0 );
            })
            ->when($param->field && $param->field != 'active' && $param->keyword, function (Builder $query) use ($param) {
                $query->where('active', 1);
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }

    /**
     * 지정 가능한 평가 설문 목록을 출력한다.
     * @param ListQueryParam $param
     * @param EvalTargetQueryParam $target
     * @return PageCollection
     * @throws HttpException
     */
    public function listEvalInfo(ListQueryParam $param, EvalTargetQueryParam $target) : PageCollection {
        $query = EvalInfo::orderBy($param->order, $param->direction)
            ->where('target', $target->target->value)
            ->where('active', $target->active   ? 1 : 0)
            ->when($param->field && $param->keyword, function (Builder $query) use ($param) {
                $query->where($param->field, $param->operator, $param->keyword);
            });
        $total = $query->count();
        $total_page = ceil($total / $param->page_per_items);
        $collection = $query->skip($param->start_rec_no)->take($param->page_per_items)
            ->get();
        return new PageCollection($total, $total_page, $collection);
    }
}
