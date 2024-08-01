<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ListUer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list {--S|show-initial-password : 초기 비밀번호 출력} {--P|page= : 목록 페이지 번호} {--F|filter= : 검색 조건(Where 뒤에 추가할 내용)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '관리를 위한 사용자 목록을 출력합니다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        try {
            $page = $this->option('page');
            if(!$page || !is_numeric($page)) $page = 1;
            $filter = $this->option('filter');
            $result = User::query()
                ->when($filter, function(Builder $query) use($filter) {
                    $query->whereRaw($filter);
                })
                ->skip(($page-1) * 20)->take(20)
                ->get();
            if($result->isNotEmpty()) {
                $data = [];
                if($this->hasOption('show-initial-password') && $this->option('show-initial-password')) {
                    foreach($result as $r) {
                        if($r instanceof User) {
                            $data[] = [
                                $r->id,
                                $r->id_alias,
                                $r->management_org_id ? User::findMe($r->management_org_id)->id_alias : '',
                                $r->name,
                                $r->email,
                                $r->getInitialPassword(),
                                $r->api_token,
                                $r->active == 1 ? 'active' : 'inactive'
                            ];
                        }
                    }
                    $this->info("page : {$page}     Number of Item : {$result->count()}");
                    $this->table(['ID', 'ID Alias', 'Manager', 'Name', 'Email', 'Password', 'API Token', 'Active'], $data);
                }
                else {
                    foreach($result as $r) {
                        $data[] = [
                            $r->id,
                            $r->id_alias,
                            $r->management_org_id ? User::findMe($r->management_org_id)->id_alias : '',
                            $r->name,
                            $r->email,
                            $r->api_token,
                            $r->active == 1 ? 'active' : 'inactive'
                        ];
                    }
                    $this->info("page : {$page}     Number of Item : {$result->count()}");
                    $this->table(['ID', 'ID Alias', 'Manager', 'Name', 'Email', 'API Token', 'Active'], $data);
                }
            }
            else $this->info('자료가 없습니다.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
