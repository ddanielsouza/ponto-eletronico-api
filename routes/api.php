<?php

use Illuminate\Http\Request;

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

Route::post('login', 'UserController@authenticate');
Route::get('refresh', 'UserController@refresh');

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('/check-token', function (){
        return response()->json(['success' => true]);
    });

    Route::get('user', 'UserController@getAuthenticatedUser');
    Route::post('logout', 'UserController@logout');
    Route::group(['prefix'=>'users'], function(){
        Route::get('/', 'UserController@getUsers');
        Route::post('/', 'UserController@register');
        Route::put('/{idUser}', 'UserController@update');
    });
    Route::group(['prefix'=>'batida-ponto'], function(){
        Route::post('/', 'BatidaPontoController@registrarPonto');
        Route::get('/horas-trabalhadas', 'BatidaPontoController@horasTrabalhadas');
    });
});