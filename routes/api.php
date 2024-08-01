<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

foreach( File::allFiles( __DIR__ . '/api/v1' ) as $route_file_v1  ) {
    require $route_file_v1->getPathname();
}

foreach( File::allFiles( __DIR__ . '/api/v2' ) as $route_file_v2  ) {
    require $route_file_v2->getPathname();
}
