<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\V1\OrderedTaskReportDto;
use App\DTOs\V1\OrderTaskDto;
use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\V1\List\OrderedTasks;
use App\Http\QueryParams\ListQueryParam;
use App\Http\Requests\V1\RequestOrderTask;
use App\Http\Requests\V1\RequestReportForOrderedTask;
use App\Lib\PageResult;
use App\Models\OrderTask;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Services\V1\MonitoringService;
use Illuminate\Http\JsonResponse;

class MonitoringController extends Controller {
    /**
     * 국내 관리기관 소속 실무자에게 요청한 업무 요청정보 목록을 출력한다.
     * @param ListQueryParam $param
     * @return JsonResponse
     */
    public function listOrderedTask(ListQueryParam $param) : JsonResponse {
        try {
            $service = MonitoringService::getInstance();
            return new OrderedTasks(new PageResult($service->listOrderedTask($param), $param));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 요청한 업무정보를 출력한다.
     * @param OrderTask $id
     * @return JsonResponse
     */
    public function getOrderedTask(OrderTask $id) : JsonResponse {
        try {
            $service = MonitoringService::getInstance();
            return new Data($service->getOrderedTask($id)->toInfoArray());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    /**
     * 업무 요청 내용을 등록한다.
     * @param RequestOrderTask $request
     * @return JsonResponse
     */
    public function orderTask(RequestOrderTask $request) : JsonResponse {
        try {
            $service = MonitoringService::getInstance();
            $dto = OrderTaskDto::createFromRequest($request);
            $service->orderTask($dto);
            return new Message();
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    public function listTaskOrdered(ListQueryParam $param) : JsonResponse {
        try {
            $service = MonitoringService::getInstance();
            return new OrderedTasks(new PageResult($service->listTaskOrdered($param), $param));
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }

    public function addReport(RequestReportForOrderedTask $request, OrderTask $id) : JsonResponse {
        try {
            $service = MonitoringService::getInstance();
            $dto = OrderedTaskReportDto::createFromRequest($request);
            $service->addReport($dto, $id);
            return new Message();
        } catch (HttpErrorsException $e) {
            return new ErrorMessage($e->getErrors(), $e->getCode());
        } catch (HttpException $e) {
            return new Message($e->getCode());
        } catch (\Exception $e) {
            return new Message(500);
        }
    }
}
