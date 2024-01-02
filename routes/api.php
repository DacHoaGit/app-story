<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/binhluan', [ApiController::class, 'binhluan']);
    Route::get('/slider', [ApiController::class, 'slider']);
    Route::get('/checktruyentheodoi', [ApiController::class, 'checktruyentheodoi']);
    Route::get('/danhsachchuong', [ApiController::class, 'danhsachchuong']);
    Route::get('/noidungtruyen', [ApiController::class, 'noidungtruyen']);
    Route::get('/theloaitruyen', [ApiController::class, 'theloaitruyen']);
    Route::post('/thembinhluan', [ApiController::class, 'thembinhluan']);
    Route::get('/timkiem', [ApiController::class, 'timkiem']);
    Route::get('/truyentheodoiuser', [ApiController::class, 'truyentheodoiuser']);

});
