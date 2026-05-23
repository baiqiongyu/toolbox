<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PccController;
use App\Http\Controllers\RakutenController;
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
    Route::get('/rakuten', [RakutenController::class, 'index'])->name('tools.rakuten');
    Route::post('/rakuten/track', [RakutenController::class, 'track'])->name('tools.rakuten.track');
});

require __DIR__.'/auth.php';
