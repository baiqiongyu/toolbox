<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PccController;
use App\Http\Controllers\CustomsController;
use App\Http\Controllers\HualeiController;
use App\Http\Controllers\RakutenController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('tools')->group(function () {
    Route::get('/pcc', [PccController::class, 'index'])->name('tools.pcc');
    Route::post('/pcc/validate', [PccController::class, 'validate'])->name('tools.pcc.validate');
    Route::get('/pcc/status/{taskId}', [PccController::class, 'status'])->name('tools.pcc.status');
    Route::get('/pcc/download/{downloadId}', [PccController::class, 'download'])->name('tools.pcc.download');
    Route::get('/pcc/download-fail/{downloadId}', [PccController::class, 'downloadFail'])->name('tools.pcc.downloadFail');
    Route::post('/pcc/check-single', [PccController::class, 'checkSingle'])->name('tools.pcc.checkSingle');
    Route::get('/rakuten', [RakutenController::class, 'index'])->name('tools.rakuten');
    Route::post('/rakuten/track', [RakutenController::class, 'track'])->name('tools.rakuten.track');
    Route::get('/customs', [CustomsController::class, 'index'])->name('tools.customs');
    Route::post('/customs/track', [CustomsController::class, 'track'])->name('tools.customs.track');
    Route::get('/hualei', [HualeiController::class, 'index'])->name('tools.hualei');
    Route::post('/hualei/track', [HualeiController::class, 'track'])->name('tools.hualei.track');
});

// ===== 用户管理（仅管理员） =====
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
