<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return 'ok';
});


Route::prefix('users')->group(function () {
    Route::get('', [UserController::class, 'index'])->name('users.getAll');
    Route::get('{id}', [UserController::class, 'show'])->name('users.getById');
    Route::post('', [UserController::class, 'register'])->name('users.register');

});
