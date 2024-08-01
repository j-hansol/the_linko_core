<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call(DefaultCountrySeeder::class);
        $this->call(DefaultWorkerJobSeeder::class);
        $this->call(DefaultUserSeeder::class);
        $this->call(DefaultVisaDocumentType::class);
    }
}
