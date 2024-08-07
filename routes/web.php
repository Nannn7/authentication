<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\AuthenticationController;

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

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticationController::class, 'create'])->name('login');
        Route::post('login', [AuthenticationController::class, 'store']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('logout', [AuthenticationController::class, 'destroy'])->name('logout');
    });
