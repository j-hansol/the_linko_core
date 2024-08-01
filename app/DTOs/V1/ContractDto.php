<?php

namespace App\DTOs\V1;

use App\Lib\ContractStatus;
use App\Lib\ContractType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ContractDto {
    // 속성
    private ?int $mediation_user_id;
    private ?Carbon $contract_date;
    private ContractStatus $status = ContractStatus::REGISTERED;

    // Setter, Getter
    public function getRecipientUserId() : int {return $this->recipient_user_id;}
    public function setMeditationUserId(int $id) : void {$this->mediation_user_id = $id;}
    public function getMeditationUserId() : ?int {return $this->mediation_user_id;}
    public function getOccupationalGroupId() : int {return $this->occupational_group_id;}
    public function getType() : ContractType {return $this->type;}
    public function getTitle() : string {return $this->title;}
    public function getBody() : string {return $this->body;}
    public function getWorkerCount() : int {return $this->worker_count;}
    public function setContractDate(Carbon $date) : void {$this->contract_date = $date;}
    public function getContractDate() : ?Carbon {return $this->contract_date;}
    public function getStatus() : ContractStatus {return $this->status;}

    // Creator

    /**
     * @param int $recipient_user_id
     * @param int $occupational_group_id
     * @param string $title
     * @param string $body
     * @param ContractType $type
     * @param int $worker_count
     * @throws HttpErrorsException
     */
    function __construct(
        private readonly int $recipient_user_id,
        private readonly int $occupational_group_id,
        private readonly string $title,
        private readonly string $body,
        private readonly ContractType $type,
        private readonly int $worker_count
    ) {
        if($this->type == ContractType::DIRECT && !$this->recipient_user_id)
            throw HttpErrorsException::getInstance([__('errors.contract.invalid_type')], 400);
    }

    /**
     * 요청데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return ContractDto
     * @throws HttpException
     */
    public static function createFromRequest(Request $request) : ContractDto {
        $type = $request->enum('type', ContractType::class);
        $dto = new static(
            $type == ContractType::DIRECT ? $request->input('recipient_user_id') : 2,
            $request->input('occupational_group_id'),
            $request->input('title'),
            $request->input('body'),
            $type,
            $request->input('worker_count')
        );
        $dto->setContractDate(Carbon::createFromFormat('Y-m-d H:i:s', $request->input('contract_date')));
        if($type == ContractType::INTERMEDIARY) {
            $dto->setMeditationUserId(2);
        }
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'recipient_user_id' => $this->recipient_user_id,
            'occupational_group_id' => $this->occupational_group_id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type->value,
            'worker_count' => $this->worker_count,
            'mediation_user_id' => $this->mediation_user_id,
            'contract_date' => $this->contract_date->format('Y-m-d H:i:s'),
            'status' => $this->status->value
        ];
    }
}
