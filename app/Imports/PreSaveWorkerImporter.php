<?php

namespace App\Imports;

use App\DTOs\V1\PreSaveWorkerDto;
use App\Lib\MemberType;
use App\Models\PreSaveWorkerInfo;
use App\Models\User;
use App\Traits\Common\CreateWorkerAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PreSaveWorkerImporter implements ToCollection, WithHeadingRow {
    use CreateWorkerAccount;

    public int $total = 0;
    public int $errors = 0;
    public int $success = 0;

    function __construct(private User $manager, private bool $create_account = true) {}

    /**
     * 콜랙션으로부터
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection) {
        foreach($collection as $crow) {
            $row = $crow->toArray();
            $birthday = get_date_from_format($row['birthday'] ?? null);
            $row['management_org_id'] = $this->manager->id;
            $row['birthday'] = $birthday?->format('Y-m-d');
            $this->total++;

            $continue_validator = Validator::make($row, [
                'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
                'cell_phone' => ['required'],
                'address' => ['nullable'],
                'family_name' => ['required'],
                'given_names' => ['required'],
                'sex' => ['required', 'in:M,F'],
                'birthday' => ['nullable', 'date'],
            ]);
            if($continue_validator->fails()) {
                $this->errors++;
                continue;
            }

            DB::beginTransaction();
            try {
                $dto = PreSaveWorkerDto::createFromArray($row);
                if($dto->getCreateAccount()) {
                    $user = $this->_createAccount($dto, $this->manager, MemberType::TYPE_FOREIGN_PERSON);
                    $fill['user_id'] = $user?->id;
                }
                PreSaveWorkerInfo::create($row);
                $this->success++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }

    public function headingRow(): int {
        return 1;
    }
}
