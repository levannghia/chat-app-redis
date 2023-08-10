<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();
Route::get('/', [AppController::class,'index'])->middleware('auth');
Route::get('/message',[MessageController::class,'index'])->middleware('auth');
Route::post('/message',[MessageController::class,'store'])->middleware('auth');
Route::post('/reactions',[ReactionController::class,'react'])->middleware('auth');
Route::get('/{any}', [AppController::class,'index'])->where('any', '.*')->middleware('auth');
Route::resource('room',RoomController::class)->middleware('auth');
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
