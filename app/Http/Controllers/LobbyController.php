<?php

namespace App\Http\Controllers;

use App\Events\ChangeCharacterEvent;
use App\Events\ChangeGameModeEvent;
use App\Events\GameMessageEvent;
use App\Events\GameMoveEvent;
use App\Events\NewMessageEvent;
use App\Events\RestartGameEvent;
use App\Events\StartGameEvent;
use App\User;
use App\Lobby;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LobbyController extends Controller
{
    private function getUser() {
        if(Auth::check()){
            return  Auth::user();
        }
        else{
            return null;
        }
    }

    public function index()
    {
        return view('index', [

        ]);
    }

    public function newMessage(Request $request)
    {
        $user = $this->getUser();
        if ($user !== null) {
            $message = strip_tags($request->input('message'));
            $lobbyId = strip_tags($request->input('lobbyId'));
            broadcast(new NewMessageEvent($user->name, $message, $lobbyId))->toOthers();
        }
    }

    public function startLobby(Request $request)
    {
        $user = $this->getUser();
        // If the user is already in a lobby, delete and detach all other lobbies
        if ($user->lobbies->count() > 0) {
            $lobbies = $user->lobbies()->get();
            foreach ($lobbies as $lobby) {
                $lobby->users()->detach();
                $lobby->delete();
            }
        }
        // Generate a new lobby URL
        $lobbyName = uniqid();
        // Check if there is a lobby already with that URL
        $lobby = Lobby::where('url', $lobbyName)->first();

        while ($lobby) {
            $lobbyName = uniqid();
            $lobby = Lobby::where('url', $lobbyName)->first();
        }

        $lobby = new Lobby;
        $lobby->url = $lobbyName;
        $lobby->save();

        return redirect()->route('lobby', ['id' => $lobbyName]);
    }

    public function setGameMode(Request $request, $lobbyId) {
        $gameMode = $request->input('gameMode');
        $lobby = Lobby::where('url', $lobbyId)->first();
        if (!$lobby) {
            abort(403, 'Lobby does not exist');
        } else {
            $lobby->gameMode = $gameMode;
            $lobby->save();
            broadcast(new ChangeGameModeEvent($lobbyId, $gameMode))->toOthers();
        }
    }

    public function lobby(Request $request, $lobbyId)
    {
        $lobby = Lobby::where('url', $lobbyId)->first();
        if (!$lobby) {
            abort(403, 'Lobby does not exist');
        } else {
            $user = $this->getUser();
            // Detach all lobbies if user is still in any
            if ($user->lobbies()->count() > 0) {
                $user->lobbies()->detach();
            }

            // If the user is not in the lobby try to attach
            // If there are already 2 users, then the lobby is full
            if (!$lobby->users->contains($user)) {
                if ($lobby->users->count() < 2 ) {
                    $user->lobbies()->attach($lobby);
                } else {
                    abort(403, 'Lobby is full');
                }
            }

            // View
            return view('lobby', [
                'lobby' => $lobby
            ]);
        }

        // If none of the above returns a view, redirect to index
        return redirect()->route('index');
    }

}
