<?php

namespace App\Console\Commands;

use App\Models\Password;
use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Mockery\Generator\StringManipulation\Pass\Pass;

class ChangeUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-password {user : 변경 대상 회원의 회원코드}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '회원의 비밀번호를 변경합니다.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $id_alias = $this->argument('user');
        $user = User::findByIdAlias($id_alias);
        if($user) {
            $password = $this->secret('비밀번호를 입력하세요.');
            $retype_password = $this->secret('비밀번호를 다시 한번 더 입력하세요.');
            $hashed_password = Hash::make($password);
            if($password == $retype_password) {
                $user->password = $hashed_password;
                $user->save();
                PasswordHistory::createByUser($user, $hashed_password);
                $this->info('변경되었습니다.');
            }
            else {
                $this->error('비밀번호가 일치하지 않습니다.');
                return Command::FAILURE;
            }
        }
        else {
            $this->error('존재하지 않는 회원코드입니다.');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
