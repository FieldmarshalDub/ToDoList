<?php

use Illuminate\Support\Facades\Route;
use App\User;

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
Route::auth();
Route::get('logout', 'Auth\LoginController@logout');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', function () {
    return view('home');
});
Route::name('boards.')->prefix('boards')->group(function () {
    Route::get('/download', 'BoardController@download')->name('download');
    Route::post('/', 'BoardController@store')->name('store');
    Route::get('/', 'BoardController@index')->name('index');
    Route::post('/{board_id}/edit', 'BoardController@update')->name('update');
    Route::delete('/{board}', 'BoardController@destroy')->name('destroy');

    Route::name('tasks.')->prefix('{board_id}/tasks')->group(function () {
        Route::get('/', 'TaskController@index')->name('index');
        Route::post('/store', 'TaskController@store')->name('store');
        Route::post('/{task_id}/update', 'TaskController@update')->name('update');
        Route::post('/{task_id}/copy', 'TaskController@copy')->name('copy');
        Route::post('/{task_id}/move', 'TaskController@move')->name('move');
        Route::delete('/{task_id}/destroy', 'TaskController@destroy')->name('destroy');
    });
});
