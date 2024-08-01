<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Message;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestWorkerActionPoint;
use App\Http\Requests\V1\RequestWriteTaskReport;
use App\Models\AssignedWorker;
use App\Services\Common\HttpException;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="worker_monitoring",
 *     description="근로자 실태 관리"
 * )
 */
class WorkerMonitoringController extends Controller {
    /**
     * 실무자 본인에게 배정된 근로자 목록을 리턴한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listAssignedWorker(ListQueryParam $param) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 배정 근로자의 활동지점 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listWorkerActionPoint(ListQueryParam $param) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }
    /**
     * 배정된 근로자의 활동지점을 설정한다.
     * @param RequestWorkerActionPoint $request
     * @param AssignedWorker $id
     * @return JsonResponse
     */
    public function setWorkerActionPoint(RequestWorkerActionPoint $request, AssignedWorker $id) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 실무자 본인이 등록한 실태 조사 보고 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listTaskReport(ListQueryParam $param) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 배정 근로자에 대한 실태보고 목록을 출력한다.
     * @param ListQueryParam $param
     * @param AssignedWorker $id
     * @return JsonResponse
     */
    public function listTaskReportForWorker(ListQueryParam $param, AssignedWorker $id) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 배정된 업무 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listTask(ListQueryParam $param) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 배정된 모든(근무중) 근로자의 보고 또는 신고, 요청 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listWorkerReport(ListQueryParam $param) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 관리 중인 지정 근로자가 등록한 보고 또는 신고, 요청 목록을 출력한다.
     * @param ListQueryParam $param
     * @param AssignedWorker $id
     * @return JsonResponse
     */
    public function listWorkerReportForWorker(ListQueryParam $param, AssignedWorker $id) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 지정 배정 근로자에 대한 실태 보고서를 작성한다.
     * @param RequestWriteTaskReport $report
     * @param AssignedWorker $id
     * @return JsonResponse
     */
    public function writeTaskReport(RequestWriteTaskReport $report, AssignedWorker $id) : JsonResponse {
        try {
            return new Message();
        }
        catch (HttpException $e) {
            return new Message($e->getCode());
        }
        catch (\Exception $e) {
            return new Message(500);
        }
    }
}
