<?php

use App\Http\Controllers\Settings\LocaleController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::redirect('settings/profile', '/settings')->name('settings.redirect');
    Route::redirect('settings/security', '/settings');
    Route::redirect('settings/appearance', '/settings');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::patch('settings/locale', [LocaleController::class, 'update'])->name('locale.update');
});
