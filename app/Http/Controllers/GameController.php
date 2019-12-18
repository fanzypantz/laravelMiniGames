<?php

namespace App\Http\Controllers;

use App\Events\ChangeCharacterEvent;
use App\Events\ChangeGameModeEvent;
use App\Events\GameMessageEvent;
use App\Events\GameMoveEvent;
use App\Events\RestartGameEvent;
use App\Events\StartGameEvent;
use App\Events\StopGameEvent;
use App\Lobby;
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
        $lobby = Lobby::where('url', $lobbyId)->first();
        if (!$lobby) {
            abort(403, 'Lobby does not exist');
        } else {
            $lobby->gameState = $game;
            $lobby->save();
            broadcast(new StartGameEvent($lobbyId, $game))->toOthers();
        }
    }

    public function stopGame(Request $request, $lobbyId)
    {
        $lobby = Lobby::where('url', $lobbyId)->first();
        if (!$lobby) {
            abort(403, 'Lobby does not exist');
        } else {
            $lobby->gameMode = '';
            $lobby->gameState = null;
            $lobby->save();
            broadcast(new StopGameEvent($lobbyId));
        }
    }

    public function restartGame(Request $request, $lobbyId)
    {
        broadcast(new RestartGameEvent($lobbyId))->toOthers();
    }

    public function gameMove(Request $request, $lobbyId)
    {
        $move = $request->input('move');
        $gameState = $request->input('gameState');

        $lobby = Lobby::where('url', $lobbyId)->first();
        if (!$lobby) {
            abort(403, 'Lobby does not exist');
        } else {
            $lobby->gameState = $gameState;
            $lobby->save();
            broadcast(new GameMoveEvent($lobbyId, $move, $gameState))->toOthers();
        }
    }

    public function gameMessage(Request $request, $lobbyId)
    {
        $roll = $request->input('message');
        broadcast(new GameMessageEvent($lobbyId, $roll))->toOthers();
    }

}

