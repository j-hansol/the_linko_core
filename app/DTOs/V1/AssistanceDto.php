<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssistanceDto {
    // 속성
    private ?int $consulting_user_id = null;

    // Setter, Getter
    public function setConsultingUserId(?int $id) : void {$this->consulting_user_id = $id;}
    public function getConsultingUserId() : ?int {return $this->consulting_user_id;}
    public function getAssistantName() : string {return $this->assistant_name;}
    public function getAssistantBirthday() : Carbon {return $this->assistant_birthday;}
    public function getAssistantTelephone() : string {return $this->assistant_telephone;}
    public function getAssistantRelationship() : ?string {return $this->assistant_relationship;}

    // Creator
    function __construct(
        private readonly string $assistant_name,
        private readonly Carbon $assistant_birthday,
        private readonly string $assistant_telephone,
        private readonly string $assistant_relationship
    ) {}

    /**
     * 요청 데이터로부터 DTO 객에를 생성한다.
     * @param Request $request
     * @return AssistanceDto
     */
    public static function createFromRequest(Request $request) : AssistanceDto {
        $dto = new static(
            $request->input('assistant_name'),
            Carbon::createFromFormat('Y-m-d', $request->input('assistant_birthday')),
            CryptData::decrypt($request->input('assistant_telephone'), 'assistant_telephone'),
            $request->input('assistant_relationship'),
        );

        $dto->setConsultingUserId($request->input('consulting_user_id'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'consulting_user_id' => $this->consulting_user_id,
            'assistant_name' => $this->assistant_name,
            'assistant_birthday' => $this->assistant_birthday->format('Y-m-d'),
            'assistant_telephone' => $this->assistant_telephone,
            'assistant_relationship' => $this->assistant_relationship
        ];
    }
}
