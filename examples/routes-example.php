<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SqlPracticeController;

/*
|--------------------------------------------------------------------------
| SQL Practice Routes
|--------------------------------------------------------------------------
|
| Add these routes to your web.php or create a separate route file
| for the SQL practice functionality.
|
*/

Route::group(['prefix' => 'sql-practice', 'middleware' => 'web'], function () {
    
    // Main practice interface
    Route::get('/', [SqlPracticeController::class, 'index'])->name('sql.practice');
    
    // Execute SQL command
    Route::post('/execute', [SqlPracticeController::class, 'execute'])->name('sql.execute');
    
    // Get database schema
    Route::get('/schema', [SqlPracticeController::class, 'schema'])->name('sql.schema');
    
    // Reset database
    Route::post('/reset', [SqlPracticeController::class, 'reset'])->name('sql.reset');
    
    // Get sample queries
    Route::get('/samples', [SqlPracticeController::class, 'samples'])->name('sql.samples');
    
});

// Optional: Add authentication middleware if needed
// Route::group(['prefix' => 'sql-practice', 'middleware' => ['web', 'auth']], function () {
//     // ... same routes
// });