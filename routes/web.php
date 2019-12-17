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

Route::get('/', 'LobbyController@index')->name('index');


Auth::routes();
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::post('lobby/message/', 'LobbyController@newMessage')->name('newMessage');

    Route::post('startLobby', 'LobbyController@startLobby')->name('startLobby');
    Route::get('lobby/{id}', 'LobbyController@lobby')->name('lobby');
    Route::post('lobby/updateCharacter/{lobby_id}', 'LobbyController@updateCharacter')->name('updateCharacter');
    Route::post('game/startGame/{lobby_id}', 'LobbyController@startGame')->name('startGame');
    Route::post('game/restartGame/{lobby_id}', 'LobbyController@restartGame')->name('restartGame');
    Route::post('game/gameMove/{lobby_id}', 'LobbyController@gameMove')->name('gameMove');
    Route::post('game/gameMessage/{lobby_id}', 'LobbyController@gameMessage')->name('gameMessage');
});
