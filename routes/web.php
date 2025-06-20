<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Health check endpoints (also available via web for monitoring tools)
Route::get('/health', [HealthController::class, 'basic']);
Route::get('/health/detailed', [HealthController::class, 'detailed']);
