<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;

class FundingDetailDto {
    // 속성
    private ?string $payer_name;
    private ?string $payer_relationship;
    private ?string $support_type;
    private ?string $payer_contact;

    // Setter, Getter
    public function getTravelCosts() : ?float {return $this->travel_costs;}
    public function setPayerName(?string $name) : void {$this->payer_name = $name;}
    public function getPayerName() : ?string {return $this->payer_name;}
    public function setPayerRelationship(?string $relationship) : void {$this->payer_relationship = $relationship;}
    public function getPayerRelationship() : ?string {return $this->payer_relationship;}
    public function setSupportType(?string $type) : void {$this->support_type = $type;}
    public function getSupportType() : ?string {return $this->support_type;}
    public function setPayerContact(?string $contact, ?string $label) : void {
        $this->payer_contact = $contact ? CryptData::decrypt($contact, $label) : null;}
    public function getPayerContact() : ?string {return $this->payer_contact;}

    // Creator
    function __construct(
        private readonly float $travel_costs
    ) {}

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return FundingDetailDto
     */
    public static function createFromRequest(Request $request) : FundingDetailDto {
        $dto = new static($request->float('travel_costs'));
        $dto->setPayerName($request->input('payer_name'));
        $dto->setPayerRelationship($request->input('payer_relationship'));
        $dto->setSupportType($request->input('support_type'));
        $dto->setPayerContact($request->input('payer_contact'), 'payer_contact');
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'travel_costs' => $this->travel_costs,
            'payer_name' => $this->payer_name,
            'payer_relationship' => $this->payer_relationship,
            'support_type' => $this->support_type,
            'payer_contact' => $this->payer_contact
        ];
    }
}
