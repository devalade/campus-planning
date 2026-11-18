<?php

use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\SpecialDayController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', CalendarController::class)->name('dashboard');

    Route::resource('courses', CourseController::class)->except(['index', 'show']);

    Route::middleware('can:manage-special-days')->prefix('admin/special-days')->name('admin.special-days')->group(function () {
        Route::get('/', [SpecialDayController::class, 'index']);
        Route::post('/', [SpecialDayController::class, 'store'])->name('.store');
        Route::delete('{specialDay}', [SpecialDayController::class, 'destroy'])->name('.destroy');
    });

    Route::middleware('can:manage-groups')->prefix('admin/groups')->name('admin.groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store'])->name('.store');
        Route::get('{group}/edit', [GroupController::class, 'edit'])->name('.edit');
        Route::put('{group}', [GroupController::class, 'update'])->name('.update');
        Route::delete('{group}', [GroupController::class, 'destroy'])->name('.destroy');
    });
});

require __DIR__.'/settings.php';
