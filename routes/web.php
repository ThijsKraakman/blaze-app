<?php

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

Route::group(['middleware' => ['auth']], function () {
    Route::resource('sounds', 'SoundController')->except('show');
    // Route::resource('profile', 'ProfileController')->except('index');
    Route::get('profile/{user}', 'ProfileController@show')->name('profile.show');
});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
