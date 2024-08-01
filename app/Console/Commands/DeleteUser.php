<?php

namespace App\Console\Commands;

use App\Lib\MemberType;
use App\Models\User;
use App\Models\WorkerEducation;
use App\Models\WorkerExperience;
use App\Models\WorkerResume;
use App\Models\WorkerVisaDocument;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {--T|type= : 회원 유형} {--E|exclude-manager= : 제외 대상 소속 메니저}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '회원을 삭제합니다. 매우 주의가 필요합니다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $manager = null;
        $type = null;

        if($this->hasOption('type') && $this->option('type')) $type = MemberType::tryFrom($this->option('type'));
        if(!$type) {
            $types = MemberType::cases();
            $names = [];
            foreach($types as $t) $names[] = $t->value . ':' . $t->name;
            $names[] = '0:Cancel';
            $typename = $this->choice('회원 유형을 선택하세요.', $names);
            $value = explode(':', $typename)[0];
            if($value == '0') {
                $this->info('계정 삭제를 중지합니다.');
                return;
            }
            $type = MemberType::tryFrom($value);
            if(!$type) {
                $this->error('회원 유형을 가져오는 중 문제가 발생했습니다.');
                return;
            }
        }

        if($this->hasOption('exclude-manager') && $this->option('exclude-manager'))
            $manager = User::findByIdAlias($this->option('exclude-manager'));

        $query = User::query()
            ->select('users.*')
            ->join('user_types', 'users.id', '=', 'user_types.user_id')
            ->where('users.management_org_id', '<>', $manager->id)
            ->where('user_types.type', $type->value);
        $target_count = $query->count();
        $total_page = ceil($target_count / 100);
        $this->info("총 삭제 대상 회원 : {$target_count}");
        for($i = 0 ; $i < $total_page ; $i++) {
            $result = $query->skip($i * 100)->take(100)->get();
            foreach ($result as $user) {
                print "\n{$user->id} - {$user->name} ({$user->id_alias}) : ";
                $this->_deleteWorkerEducation($user);
                $this->_deleteWorkerExperience($user);
                $this->_deleteWorkerResume($user);
                $this->_deleteWorkerVisaDocument($user);
                $user->deleteFileResource();
                $user->forceDelete();
                print '삭제됨';
            }
        }
        print "\n";
    }

    private function _deleteWorkerEducation(User $user) : void {
        $educations = WorkerEducation::findAllByUser($user);
        if($educations->isNotEmpty()) {
            print "학력정보({$educations->count()}) ";
            foreach($educations as $education) {
                $education->delete();
            }
        }
    }

    private function _deleteWorkerExperience(User $user) : void {
        $experiences = WorkerExperience::findAllByUser($user);
        if($experiences->isNotEmpty()) {
            print "경력정보({$experiences->count()}) ";
            foreach($experiences as $experience) {
                $experience->delete();
            }
        }
    }

    private function _deleteWorkerResume(User $user) : void {
        $resumes = WorkerResume::findAllByUser($user);
        if($resumes->isNotEmpty()) {
            print "이력서파일({$resumes->count()}) ";
            foreach($resumes as $resume) {
                $resume->delete();
            }
        }
    }

    private function _deleteWorkerVisaDocument(User $user) : void {
        $documents = WorkerVisaDocument::findAllByUser($user);
        if($documents->isNotEmpty()) {
            print "비자신청서류({$documents->count()}) ";
            foreach($documents as $document) {
                $document->delete();
            }
        }
    }
}
