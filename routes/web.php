<?php

use App\Http\Controllers\ActivityAnalysisController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FitnessProfileController;
use App\Http\Controllers\MealAnalysisController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeGenerationController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\VoiceTranscriptionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'edit'])->name('onboarding.edit');
    Route::post('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

    Route::middleware('profile.complete')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('fitness-profile', [FitnessProfileController::class, 'edit'])->name('fitness.edit');
        Route::put('fitness-profile', [FitnessProfileController::class, 'update'])->name('fitness.update');
        Route::patch('fitness-profile/weight', [FitnessProfileController::class, 'updateWeight'])->name('fitness.weight');
        Route::patch('fitness-profile/goal-adjustment', [FitnessProfileController::class, 'updateGoalAdjustment'])->name('fitness.goalAdjustment');
        Route::post('meals/analyze', [MealAnalysisController::class, 'store'])->name('meals.analyze')->middleware('throttle:20,60');
        Route::resource('meals', MealController::class)->except(['show', 'create', 'index']);

        Route::post('voice/transcribe', [VoiceTranscriptionController::class, 'store'])->name('voice.transcribe')->middleware('throttle:20,60');

        Route::post('activities/analyze', [ActivityAnalysisController::class, 'store'])->name('activities.analyze')->middleware('throttle:20,60');
        Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::delete('activities/{activityLog}', [ActivityController::class, 'destroy'])->name('activities.destroy');

        Route::post('tutorial/dismiss', [TutorialController::class, 'dismiss'])->name('tutorial.dismiss');

        Route::post('recipes/generate', [RecipeGenerationController::class, 'store'])->name('recipes.generate')->middleware('throttle:10,60');
        Route::post('recipes/{recipe}/eat', [RecipeController::class, 'eat'])->name('recipes.eat');
        Route::resource('recipes', RecipeController::class)->only(['index', 'store', 'show', 'destroy']);
    });
});

require __DIR__.'/settings.php';
