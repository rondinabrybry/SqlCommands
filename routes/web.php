<?php

use Illuminate\Support\Facades\Route;
use SqlCommands\Http\Controllers\SqlPracticeController;

/*
|--------------------------------------------------------------------------
| SQL Practice Routes
|--------------------------------------------------------------------------
|
| These routes are automatically loaded by the SqlCommands package.
| They provide the web interface for SQL practice and learning.
|
*/

Route::group([
    'prefix' => config('sql-commands.route_prefix', 'sql-practice'),
    'middleware' => config('sql-commands.middleware', ['web']),
    'namespace' => 'SqlCommands\Http\Controllers'
], function () {
    
    // Main practice interface
    Route::get('/', [SqlPracticeController::class, 'index'])->name('sql.practice.index');
    
    // Execute SQL command from textarea
    Route::post('/execute', [SqlPracticeController::class, 'execute'])->name('sql.practice.execute');
    
    // Get database schema for sidebar reference
    Route::get('/schema', [SqlPracticeController::class, 'schema'])->name('sql.practice.schema');
    
    // Reset database to initial state with sample data
    Route::post('/reset', [SqlPracticeController::class, 'reset'])->name('sql.practice.reset');
    
    // Get sample queries for learning
    Route::get('/samples', [SqlPracticeController::class, 'samples'])->name('sql.practice.samples');
    
    // Initialize practice database (first-time setup)
    Route::post('/initialize', [SqlPracticeController::class, 'initialize'])->name('sql.practice.initialize');
    
    // Export query results as CSV (bonus feature)
    Route::post('/export', [SqlPracticeController::class, 'export'])->name('sql.practice.export');
    
});