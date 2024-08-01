<?php

namespace App\Services\V1;

use App\Models\User;
use Exception;

class WorkerMonitoringService {
    protected ?User $user;
    protected ?User $manager;

    public function __construct() {
        $this->user = current_user();
        $this->manager = $this->user->getAffiliationManager();
    }

    /**
     * 서비스 객체를 리턴한다.
     * @return WorkerMonitoringService
     * @throws Exception
     */
    public function getInstance() : WorkerMonitoringService {
        $instance = app(static::class);
        if(!$instance) throw new Exception('service not constructed');
        return $instance;
    }
}
