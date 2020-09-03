<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register', 'Api\Auth\RegisterController')->name('register');
Route::post('login', 'Api\Auth\LoginController')->name('login');
Route::post('logout', 'Api\Auth\LogoutController')->middleware('auth:api');

Route::name('boards.')->prefix('boards')->group(function () {
    Route::get('/', 'BoardController@index')->name('index');
    Route::get('/download', 'BoardController@download')->name('download');
    Route::post('/create', 'BoardController@store')->name('store');
    Route::post('/{board_id}/edit', 'BoardController@update')->name('update');
    Route::delete('/{board_id}', 'BoardController@destroy')->name('destroy');

    Route::name('tasks.')->prefix('{board_id}/tasks')->group(function () {
        Route::get('/', 'TaskController@index')->name('index');
        Route::post('/create', 'TaskController@store')->name('store');
        Route::post('/{task_id}/update', 'TaskController@update')->name('update');
        Route::post('/{task_id}/copy', 'TaskController@copy')->name('copy');
        Route::post('/{task_id}/move', 'TaskController@move')->name('move');
        Route::delete('/{task_id}/destroy', 'TaskController@destroy')->name('destroy');
    });
});

