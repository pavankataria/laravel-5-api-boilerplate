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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/lol', function () {
    echo "THIS TEST SHOWS";
})->name('test');

Route::post('login', 'OAuthController@login');
Route::post('oauth/accesstoken', 'OAuthController@accessToken')->name('oauth.accesstoken');

Route::post('oauth/refreshtoken', 'OAuthController@refreshToken');
Route::get('test', function(){
    echo "This shows";
});