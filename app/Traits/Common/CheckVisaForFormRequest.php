<?php

namespace App\Traits\Common;

use App\Models\VisaApplication;
use App\Models\VisaVisitDetail;
use Illuminate\Validation\Validator;

trait CheckVisaForFormRequest {
    /**
     * 비자정보 및 방문정보 유무를 검사하여 에러 메시지를 추가한다.
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator) : void {
        $validator->after(function(Validator $validator) {
            $visa = $this->route()->parameter('id');
            if(!$visa) $validator->errors()->add('visa', 'required visa application info');
            elseif($visa instanceof VisaApplication) {
                $detail = VisaVisitDetail::findByVisa($visa);
                if(!$detail) $validator->errors()->add('visit_detail', 'no visit detail');
            }
            else $validator->errors()->add('visa', 'invalid visa info');
        });
    }
}
