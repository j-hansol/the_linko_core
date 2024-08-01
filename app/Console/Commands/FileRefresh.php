<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VisaDocument;
use App\Models\VisaPhoto;
use App\Models\WorkerEducation;
use App\Models\WorkerExperience;
use App\Models\WorkerResume;
use App\Models\WorkerVisaDocument;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class FileRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '불필요한 파일을 삭제합니다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->_refreshFile(User::class);
        $this->_refreshFile(WorkerEducation::class);
        $this->_refreshFile(WorkerResume::class);
        $this->_refreshFile(WorkerExperience::class);
        $this->_refreshFile(WorkerVisaDocument::class);
        $this->_refreshFile(VisaPhoto::class);
        $this->_refreshFile(VisaDocument::class);
    }

    private function _refreshFile(string $model_class) : void {
        $base_path = $model_class::basePath();
        $target_path = $this->_getTargetPath($base_path);
        $move_count = $this->_initMoveCount($base_path);

        $page = 0;
        $query = $model_class::query()
            ->orderBy('id');
        do {
            $item = $query->skip($page * 100)->take(100)->get();
            $item_count = $item->count();
            $this->_moveFile($base_path, $target_path, $move_count, $item);
            $page++;
        } while($item_count == 100);
        $this->_deleteBasePath($base_path);
        $this->_moveToBase($target_path, $base_path, $move_count);
    }

    /**
     * 이동 회수를 저장하기 위한 배열을 초기화한다.
     * @param array $base_path
     * @return array
     */
    private function _initMoveCount(array $base_path) : array {
        $move_count = [];
        foreach($base_path as $key => $value) $move_count[$key] = 0;
        return $move_count;
    }

    /**
     * 지정 경로를 이용한 대상 경로 이름을 지정한다.
     * @param array $base_path
     * @return array
     */
    private function _getTargetPath(array $base_path) : array {
        $target = [];
        foreach($base_path as $key => $path) $target[$key] = $path . '_target';
        return $target;
    }

    /**
     * 지정 경로를 삭제한다. 파일도 삭제한다.
     * @param array $bash_path
     * @return void
     */
    private function _deleteBasePath(array $bash_path) : void {
        foreach($bash_path as $path) Storage::disk('local')->deleteDirectory($path);
    }

    /**
     * 지정 경로를 기본 저장 경로로 변경한다.
     * @param array $target_path
     * @param array $base_path
     * @return void
     */
    private function _moveToBase(array $target_path, array $base_path, array $move_count) : void {
        foreach ($target_path as $key => $path) {
            if($move_count[$key] > 0) Storage::disk('local')->move($path, $base_path[$key]);
        }
    }

    /**
     * 저장된 파일들을 백업 폴드로 이동한다.
     * @param array $base_path
     * @param array $target_path
     * @param Collection $collection
     * @return void
     */
    private function _moveFile(array $base_path, array $target_path, array &$move_count, Collection $collection) : void {
        foreach($collection as $model) {
            foreach($base_path as $field => $path) {
                if($model->getAttribute($field)) {
                    $source = $model->getAttribute($field);
                    $target = str_replace($path, $target_path[$field], $source);
                    Storage::disk('local')->move($source, $target);
                    $move_count[$field]++;
                }
            }
        }
    }
}
