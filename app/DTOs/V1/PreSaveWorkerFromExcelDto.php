<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class PreSaveWorkerFromExcelDto {
    // 속성
    private ?string $work_file_path;

    // Getter
    public function getWorkFilePath() : ?string {return $this->work_file_path;}
    public function getCreateAccount() : bool {return $this->create_account;}

    // Creator
    function __construct(private readonly UploadedFile $file, private readonly bool $create_account) {
        $temp_path = $this->file->getPathname();
        $temp_target_path = tempnam(sys_get_temp_dir(), 'pre_save_') . '.' . $file->getClientOriginalExtension();
        if(!CryptData::decryptFile($temp_path, $temp_target_path)) throw new Exception('invalid encrypt data');
        $this->work_file_path = $temp_target_path;
    }

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return PreSaveWorkerFromExcelDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : PreSaveWorkerFromExcelDto {
        return new static($request->file('file'), $request->boolean('create_account'));
    }

    /**
     * 관련 파일을 삭제한다.
     * @return void
     */
    public function unLink() : void {
        unlink($this->work_file_path);
        unlink($this->file->getPathname());
    }
}
