<?php

namespace Database\Seeders;

use App\Models\VisaDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultVisaDocumentType extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $path = database_path('assets/visa_document_type.csv');
        if(!file_exists($path)) return;

        if (($handle = fopen($path, "r")) !== false) {
            if($header = fgetcsv($handle, 1000, ",")) {
                while (($data = $this->getData($handle)) !== false) {
                    if(count($data) != 8) continue;
                    $wdata = [
                        'name' => $data[1],
                        'en_name' => $data[2],
                        'description' => $data[3],
                        'en_description' => $data[4],
                        'required' => $data[5],
                        'weight' => $data[6],
                        'active' => $data[7]
                    ];
                    $type = VisaDocumentType::create($wdata);
                }
            }
            fclose($handle);
        }
    }

    private function getData($handle) : array|bool {
        $data = fgetcsv($handle, 1000, ",");
        //print_r($data);
        return $data;
    }
}
