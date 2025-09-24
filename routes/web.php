<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\InventoryController;
use App\Http\Controllers\App\ItemTypeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// --- Partie Application Utilisateur (/app) ---
Route::prefix('app')->middleware(['auth', 'verified'])->group(function () {
    // Dashboard de l'utilisateur
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('app.dashboard');

    // Routes pour la gestion de l'inventaire
    Route::get('/inventory', [InventoryController::class, 'index'])->name('app.inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('app.inventory.store');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('app.inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('app.inventory.destroy');

    Route::patch('/app/user/low-stock-threshold', [InventoryController::class, 'updateLowStockThreshold'])
        ->name('app.user.updateLowStockThreshold');

    // GESTION DES TYPES D'ITEM PAR L'UTILISATEUR
    Route::get('/item-types', [ItemTypeController::class, 'index'])->name('app.item_types.index');
    Route::post('/item-types', [ItemTypeController::class, 'store'])->name('app.item_types.store');
    Route::put('/item-types/{itemType}', [ItemTypeController::class, 'update'])->name('app.item_types.update');
    Route::delete('/item-types/{itemType}', [ItemTypeController::class, 'destroy'])->name('app.item_types.destroy');
});


// --- Partie Administration (/admin) ---
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function () {
    // Dashboard Admin
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
