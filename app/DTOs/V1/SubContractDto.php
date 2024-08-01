<?php

namespace App\DTOs\V1;

use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubContractDto {
    // 속성
    private ?Carbon $sub_contract_date;

    // Setter, Getter
    public function getSubRecipientUserId() : int {return $this->sub_recipient_user_id;}
    public function getSubTitle() : string {return $this->sub_title;}
    public function getSubBody() : string {return $this->sub_body;}
    public function setSubContractDate(Carbon $date) : void {$this->sub_contract_date = $date;}
    public function getSubContractDate() : ?Carbon {return $this->sub_contract_date;}

    // Creator
    function __construct(
        private readonly int $sub_recipient_user_id,
        private readonly string $sub_title,
        private readonly string $sub_body,
    ) {}

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Contract $contract
     * @param Request $request
     * @return SubContractDto
     */
    public static function createFromRequest(Request $request) : SubContractDto {
        $dto = new static(
            $request->input('sub_recipient_user_id'),
            $request->input('sub_title'),
            $request->input('sub_body')
        );
        $dto->setSubContractDate($request->date('sub_contract_date', 'Y-m-d'));
    }

    // for model
    public function toArray() : array {
        return [
            'sub_recipient_user_id' => $this->sub_recipient_user_id,
            'sub_title' => $this->sub_title,
            'sub_body' => $this->sub_body,
            'sub_contract_date' => $this->sub_contract_date
        ];
    }
}
