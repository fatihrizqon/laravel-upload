<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('/filepond', function () {
    return view('filepond.index');
});

Route::post('/upload', [UploadController::class, 'store'])->name('upload');
Route::delete('/revert', [UploadController::class, 'destroy'])->name('revert');

Route::post('/files', [FileController::class, 'store'])->name('files.store');