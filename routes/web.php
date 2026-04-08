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

// Protected routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Clients
    Route::resource('clients', \App\Http\Controllers\ClientController::class);

    // Projects
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    Route::post('/projects/{project}/competitors', [\App\Http\Controllers\ProjectController::class, 'syncCompetitors'])
        ->name('projects.sync-competitors');

    // Imports (CSV upload)
    Route::get('/imports', [\App\Http\Controllers\ImportController::class, 'index'])
        ->name('imports.index');
    Route::get('/imports/create', [\App\Http\Controllers\ImportController::class, 'create'])
        ->name('imports.create');
    Route::post('/imports/upload', [\App\Http\Controllers\ImportController::class, 'upload'])
        ->middleware('throttle:15,1')
        ->name('imports.upload');
    Route::post('/imports/preview', [\App\Http\Controllers\ImportController::class, 'preview'])
        ->middleware('throttle:30,1')
        ->name('imports.preview');
    Route::post('/imports/confirm', [\App\Http\Controllers\ImportController::class, 'confirm'])
        ->middleware('throttle:10,1')
        ->name('imports.confirm');
    Route::get('/imports/{snapshot}', [\App\Http\Controllers\ImportController::class, 'show'])
        ->name('imports.show');
    Route::delete('/imports/{snapshot}', [\App\Http\Controllers\ImportController::class, 'destroy'])
        ->name('imports.destroy');

    // Keywords
    Route::get('/keywords', [\App\Http\Controllers\KeywordController::class, 'index'])
        ->name('keywords.index');
    Route::get('/keywords/{keyword}/timeline', [\App\Http\Controllers\KeywordController::class, 'timeline'])
        ->name('keywords.timeline');

    // Competitors
    Route::get('/competitors', [\App\Http\Controllers\CompetitorController::class, 'index'])
        ->name('competitors.index');
    Route::get('/competitors/data', [\App\Http\Controllers\CompetitorController::class, 'data'])
        ->name('competitors.data');

    Route::post('/reports/filtered', [\App\Http\Controllers\ReportController::class, 'storeFiltered'])
        ->name('reports.store-filtered');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/create', [\App\Http\Controllers\ReportController::class, 'create'])
        ->name('reports.create');
    Route::post('/reports', [\App\Http\Controllers\ReportController::class, 'store'])
        ->name('reports.store');
    Route::get('/reports/{report}/download', [\App\Http\Controllers\ReportController::class, 'download'])
        ->name('reports.download');
    Route::delete('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'destroy'])
        ->name('reports.destroy');

    // Templates
    Route::resource('templates', \App\Http\Controllers\TemplateController::class);

    // API-style endpoints (JSON responses for AJAX)
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
