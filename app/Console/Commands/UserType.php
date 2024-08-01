<?php

namespace App\Console\Commands;

use App\Lib\MemberType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserType as Type;

class UserType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:type {id_alias : 회원 ID 별칭} {--T|type= : 추가 또는 삭제할 회원 유형} {--D|delete : 지정 유형 삭제} {--A|add : 지정 유형 추가}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '지정 계정의 회원 유형을 조회하거나 추가/삭제한다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $id_alias = $this->argument('id_alias');
        $delete_type = $this->hasOption('delete') && $this->option('delete');
        $add_type = $this->hasOption('add') && $this->option('add');
        $type = $this->option('type');

        $user = User::findByIdAlias($id_alias);
        if(!$user) {
            $this->error('지정 회원을 찾을 수 없습니다.');
            return;
        }
        $user_types = $user->getTypes()->pluck('type')->toArray();

        if($delete_type || $add_type) {
            $target_type = MemberType::tryFrom($type);
            if(!$target_type) {
                $types = MemberType::cases();
                $names = [];
                foreach($types as $t) $names[] = $t->value . ':' . $t->name;
                $names[] = '0:Cancel';
                $typename = $this->choice('회원 유형을 선택하세요.', $names);
                $value = explode(':', $typename)[0];
                if($value == '0') {
                    $this->info($delete_type ? '유형 삭제를 중단합니다.' : '유형 추가를 중단합니다.');
                    return;
                }
                $type = MemberType::tryFrom($value);
                if(!$type) {
                    $this->error('회원 유형을 가져오는 중 문제가 발생했습니다.');
                    return;
                }

                $target_type = $type;
                if($delete_type && !in_array($target_type->value, $user_types)) {
                    $this->error('기존 회원 유형이에 대상 유형이 존재하지 않습니다.');
                    $this->showStatus($user);
                    return;
                }

                if($add_type && in_array($target_type, $user_types)) {
                    $this->error('이미 대상 회원 유형이 존재합니다.');
                    $this->showStatus($user);
                    return;
                }

                try {
                    if($delete_type) $this->deleteType($user, $target_type);
                    elseif($add_type) $this->addType($user, $target_type);
                } catch (\Exception $e) {
                    $this->error('회원 유형 추가/삭제 과정에 오류가 발생했습니다.');
                }
            }
        }
        $user->getTypes();
        $this->showStatus($user);
    }

    /**
     * @param User $user
     * @param MemberType $type
     * @return void
     * @throws \Exception
     */
    private function deleteType(User $user, MemberType $type) : void {
        DB::beginTransaction();
        try {
            DB::table('user_types')
                ->where('user_id', $user->id)
                ->where('type', $type->value)
                ->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    /**
     * @param User $user
     * @param MemberType $type
     * @return void
     * @throws \Exception
     */
    private function addType(User $user, MemberType $type) : void {
        DB::beginTransaction();
        try {
            DB::table('user_types')
                ->insert([
                    'user_id' => $user->id,
                    'type' => $type->value
                ]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    private function showStatus(User $user) : void {
        $types = $user->getTypes(true)->pluck('type')->toArray();
        $data = [];
        foreach($types as $t) {
            $type_name = MemberType::tryFrom($t)->name;
            $data[] = [$t, $type_name];
        }

        $this->info("ID Alias : {$user->id_alias}");
        $this->table(['ID', 'Name'], $data);
    }
}
