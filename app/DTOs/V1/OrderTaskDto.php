<?php

namespace App\DTOs\V1;

use App\Lib\OrderedTaskType;
use App\Lib\OrderTaskStatus;
use App\Models\User;
use Illuminate\Http\Request;

class OrderTaskDto {
    function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly User $target_user) {}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 리턴한다.
     * @param Request $request
     * @return OrderTaskDto
     */
    public static function createFromRequest(Request $request) : OrderTaskDto {
        return new static(
            $request->input('title'),
            $request->input('body'),
            User::findMe($request->integer('target_user_id'))
        );
    }

    // for Model
    public function toArray() : array {
        return [
            'target_user_id' => $this->target_user->id,
            'task_type' => OrderedTaskType::OTHER,
            'title' => $this->title,
            'body' => $this->body,
            'status' => OrderTaskStatus::ORDERED
        ];
    }
}
