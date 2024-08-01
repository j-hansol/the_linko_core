<?php

namespace Database\Seeders;

use App\Models\OccupationalGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultWorkerJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $parents = [null, null, null, null, null];
        if(!file_exists(database_path('assets/job_group.txt'))) return;

        $content = file_get_contents(database_path('assets/job_group.txt'));
        $lines = explode(PHP_EOL, $content);
        foreach($lines as $line) {
            $temp = trim($line);
            if(empty($temp)) continue;
            $data = explode(' ', $temp, 2);
            $len = mb_strlen($data[0]);
            switch ($len) {
                case 5:
                    OccupationalGroup::create([
                        'group_code' => $data[0],
                        'name' => $data[1],
                        'parent_id' => $parents[4]->id,
                        'leaf_node' => true
                    ]);
                    break;
                default:
                    $parents[$len] = OccupationalGroup::create([
                        'group_code' => $data[0],
                        'name' => $data[1],
                        'parent_id' => $parents[$len - 1]?->id
                    ]);
            }
        }
    }
}
