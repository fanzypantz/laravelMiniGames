<?php

namespace App\Http\Controllers;

use App\Events\ChangeCharacterEvent;
use App\Events\GameMessageEvent;
use App\Events\GameMoveEvent;
use App\Events\RestartGameEvent;
use App\Events\StartGameEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    private function getUser() {
        if(Auth::check()){
            return  Auth::user();
        }
        else{
            return null;
        }
    }

    public function updateCharacter(Request $request, $lobbyId)
    {
        $characterName = strip_tags($request->input('character'));
        broadcast(new ChangeCharacterEvent($characterName, $lobbyId))->toOthers();
    }

    public function startGame(Request $request, $lobbyId)
    {
        $game = $request->input('game');
        broadcast(new StartGameEvent($lobbyId, $game))->toOthers();
    }

    public function restartGame(Request $request, $lobbyId)
    {
        broadcast(new RestartGameEvent($lobbyId))->toOthers();
    }

    public function gameMove(Request $request, $lobbyId)
    {
        $move = $request->input('move');
        broadcast(new GameMoveEvent($lobbyId, $move))->toOthers();
    }

    public function gameMessage(Request $request, $lobbyId)
    {
        $roll = $request->input('message');
        broadcast(new GameMessageEvent($lobbyId, $roll))->toOthers();
    }

}
