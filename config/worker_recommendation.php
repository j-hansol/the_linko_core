<?php

use App\Models\AssignedWorker;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\VisaAssistant;
use App\Models\VisaContact;
use App\Models\VisaCost;
use App\Models\VisaEducation;
use App\Models\VisaEmployment;
use App\Models\VisaFamily;
use App\Models\VisaPassport;
use App\Models\VisaProfile;
use App\Models\VisaVisitDetail;
use App\Models\WorkerActionPoint;
use App\Models\WorkerBodyPhoto;
use App\Models\WorkerFamily;
use App\Models\WorkerInfo;
use App\Models\WorkerEducation;
use App\Models\WorkerExperience;

return [
    'models' => [
        User::class => '회원 마스터 정보',
        WorkerInfo::class => '근로자 부가정보',
        WorkerBodyPhoto::class => '근로자 신체사진 정보',
        /**
        WorkerFamily::class => '근로자 가족정보',
        WorkerActionPoint::class => '근로자 활동지점 정보',
        AssignedWorker::class => '근로자 채용정보',
        Evaluation::class => '평가정보',
        VisaApplication::class => '비자신청 마스터 정보',
        VisaProfile::class => '비자신청 프로필 정보',
        VisaPassport::class => '비자신청 여권정보',
        VisaContact::class => '비자신청 연락처 정보',
        VisaEducation::class => '비자신청 학력정보',
        VisaEmployment::class => '비자신청 직업정보',
        VisaFamily::class => '비자신청 가족정보',
        VisaVisitDetail::class => '비자신청 방문정보',
        VisaCost::class => '비자신청 비용지원 정보',
        VisaAssistant::class => '비자신청 서류작성 도움정보',
        */
        WorkerEducation::class => '근로자 학력정보',
        WorkerExperience::class => '근로자 경력정보'
    ],
    'model_alias' => [
        User::class => 'user',
        WorkerInfo::class => 'worker_information',
        WorkerBodyPhoto::class => 'worker_body_photo',
        /**
        WorkerFamily::class => 'worker_family',
        WorkerActionPoint::class => 'worker_action_point',
        AssignedWorker::class => 'assigned_worker',
        Evaluation::class => 'evaluation',
        VisaApplication::class => 'visa_application',
        VisaProfile::class => 'visa_profile',
        VisaPassport::class => 'visa_passport',
        VisaContact::class => 'visa_contract',
        VisaEducation::class => 'visa_education',
        VisaEmployment::class => 'visa_employment',
        VisaFamily::class => 'visa_family',
        VisaVisitDetail::class => 'visa_visit_detail',
        VisaCost::class => 'visa_cost',
        VisaAssistant::class => 'visa_assistant',
        */
        WorkerEducation::class => 'worker_education',
        WorkerExperience::class => 'worker_experience'
    ]
];
