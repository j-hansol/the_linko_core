<?php

namespace App\Console\Commands;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Console\Command;

class DecryptFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypt:decrypt-file {in_path : 원본 파일 경로} {--out= : 출력 파일 경로}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '암호화된 파일 내용을 복호화한다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $in_path = $this->argument('in_path');
        if(!$in_path) {
            $this->error('입력 파일 경로가 누락되었습니다.');
            return;
        }

        $out_path = $this->option('out');
        if(!$out_path) {
            $info = pathinfo($in_path);
            $out_path = $info['dirname'] . '/' . $info['filename'] . '-encrypt' . '.' . $info['extension'];
        }
        if(CryptData::decryptFile($in_path, $out_path)) $this->info("복호화된 내용이 {$out_path} 로 저장되었습니다.");
        else $this->error('보호화된 내용을 저장할 수 없습니다. 원본 파일 존재 여부와 출력 파일 저장 권한 문제가 없는지 확인하세요.');
    }
}
