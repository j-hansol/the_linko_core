<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $path = database_path('assets/countries.csv');
        if(!file_exists($path)) return;

        if (($handle = fopen($path, "r")) !== false) {
            if(fgetcsv($handle, 1000, ",")) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    if(count($data) != 9) continue;
                    Country::create([
                        'name' => $data[0],
                        'en_name' => $data[1],
                        'code' => $data[2],
                        'iso3_code' => $data[3],
                        'nationality' => $data[4],
                        'continent' => $data[5],
                        'en_continent' => $data[6],
                        'language_code' => $data[7],
                        'active' => $data[8]
                    ]);
                }
            }
            fclose($handle);
        }
    }
}
