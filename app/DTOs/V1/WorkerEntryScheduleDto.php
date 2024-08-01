<?php

namespace App\DTOs\V1;

use App\Models\EntrySchedule;
use Illuminate\Http\Request;

class WorkerEntryScheduleDto {
    // 생성자
    function __construct(
        private readonly EntrySchedule $schedule,
        private readonly array $ids
    ) {}

    // Getter
    public function getEntrySchedule() : EntrySchedule {return $this->schedule;}
    public function getWorkerIds() : array {return $this->ids;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerEntryScheduleDto
     */
    public static function createFromRequest(Request $request) : WorkerEntryScheduleDto {
        return new static(
            EntrySchedule::findMe($request->integer('entry_schedule_id')),
            explode(',', $request->input('assigned_worker_ids'))
        );
    }
}
