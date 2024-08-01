<?php
use Illuminate\Support\Facades\Route;
Route::get('/', function() {
    return view('welcome');
});
Route::get('/references', function() {
    return view('references');
})->name('references');
Route::get('/enums', function() {
    return view('enums');
})->name('enums');
Route::get('/excel', function() {
    return view('excel');
})->name('excels');
Route::get('/visa_ocr', function() {
    return view('visa_ocr');
})->name('visa_ocr');
