<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — RankReport Pro
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// ── All authenticated users (admin + editor + viewer) ──────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Clients — read only
    Route::get('/clients',           [\App\Http\Controllers\ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}',  [\App\Http\Controllers\ClientController::class, 'show'])->name('clients.show');

    // Projects — read only
    Route::get('/projects',                [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}',      [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');

    // Imports — read only
    Route::get('/imports',              [\App\Http\Controllers\ImportController::class, 'index'])->name('imports.index');
    Route::get('/imports/{snapshot}',   [\App\Http\Controllers\ImportController::class, 'show'])->name('imports.show');

    // Keywords
    Route::get('/keywords', [\App\Http\Controllers\KeywordController::class, 'index'])
        ->name('keywords.index');
    Route::get('/keywords/{keyword}/timeline', [\App\Http\Controllers\KeywordController::class, 'timeline'])
        ->name('keywords.timeline');

    // Competitors
    Route::get('/competitors',       [\App\Http\Controllers\CompetitorController::class, 'index'])->name('competitors.index');
    Route::get('/competitors/data',  [\App\Http\Controllers\CompetitorController::class, 'data'])->name('competitors.data');

    // Reports — read + download
    Route::get('/reports',                    [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}/download',  [\App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');

    // Templates — read only
    Route::get('/templates',             [\App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template}',  [\App\Http\Controllers\TemplateController::class, 'show'])->name('templates.show');

    // API-style endpoints (JSON)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/projects-by-client/{client}', [\App\Http\Controllers\ProjectController::class, 'byClient'])
            ->name('projects.by-client');
        Route::get('/snapshots-by-project/{project}', [\App\Http\Controllers\ImportController::class, 'snapshotsByProject'])
            ->name('snapshots.by-project');
        Route::get('/competitors-by-project/{project}', [\App\Http\Controllers\CompetitorController::class, 'byProject'])
            ->name('competitors.by-project');
        Route::get('/dashboard-data', [\App\Http\Controllers\DashboardController::class, 'data'])
            ->name('dashboard.data');
        Route::get('/landing-pages', [\App\Http\Controllers\DashboardController::class, 'allLandingPages'])
            ->name('landing-pages');
        Route::get('/url-keywords', [\App\Http\Controllers\DashboardController::class, 'urlKeywords'])
            ->name('url-keywords');
    });

});

// ── Admin + Editor (write operations) ─────────────────────────────────────
Route::middleware(['auth', 'role:admin,editor'])->group(function () {

    // Clients — write
    Route::get('/clients/create',            [\App\Http\Controllers\ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients',                  [\App\Http\Controllers\ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit',     [\App\Http\Controllers\ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}',          [\App\Http\Controllers\ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}',       [\App\Http\Controllers\ClientController::class, 'destroy'])->name('clients.destroy');

    // Projects — write
    Route::get('/projects/create',              [\App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects',                    [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit',      [\App\Http\Controllers\ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}',           [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}',        [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/competitors', [\App\Http\Controllers\ProjectController::class, 'syncCompetitors'])
        ->name('projects.sync-competitors');

    // Imports — write/upload
    Route::get('/imports/create',       [\App\Http\Controllers\ImportController::class, 'create'])->name('imports.create');
    Route::post('/imports/upload',      [\App\Http\Controllers\ImportController::class, 'upload'])
        ->middleware('throttle:15,1')->name('imports.upload');
    Route::post('/imports/preview',     [\App\Http\Controllers\ImportController::class, 'preview'])
        ->middleware('throttle:30,1')->name('imports.preview');
    Route::post('/imports/confirm',     [\App\Http\Controllers\ImportController::class, 'confirm'])
        ->middleware('throttle:10,1')->name('imports.confirm');
    Route::delete('/imports/{snapshot}', [\App\Http\Controllers\ImportController::class, 'destroy'])
        ->name('imports.destroy');

    // Reports — create/delete
    Route::get('/reports/create',   [\App\Http\Controllers\ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports',         [\App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
    Route::post('/reports/filtered', [\App\Http\Controllers\ReportController::class, 'storeFiltered'])
        ->name('reports.store-filtered');
    Route::delete('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'destroy'])
        ->name('reports.destroy');

    // Templates — write
    Route::get('/templates/create',             [\App\Http\Controllers\TemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates',                   [\App\Http\Controllers\TemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}/edit',    [\App\Http\Controllers\TemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/templates/{template}',         [\App\Http\Controllers\TemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}',      [\App\Http\Controllers\TemplateController::class, 'destroy'])->name('templates.destroy');

});

// ── Admin only ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
