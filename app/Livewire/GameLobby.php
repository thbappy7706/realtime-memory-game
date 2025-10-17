<?php

namespace App\Livewire;

use App\Events\GameUpdated;
use App\Models\Game;
use App\Models\GamePlayer;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class GameLobby extends Component
{
    #[Validate('required', message: 'Please provide a game name')]
    #[Validate('min:3', message: 'This title is too short')]
    #[Validate('max:50', message: 'This title is too long')]
    public $gameName = '';

    public $games = [];

    public function mount(): void
    {
        $this->loadGames();
    }

    public function loadGames(): void
    {
        $this->games = Game::with(['players.user'])->where('status', 'new')->latest()->get();
    }

    public function createGame()
    {
        $this->validate();

        $game = Game::create([
            'name' => $this->gameName,
            'status' => 'new',
            'max_players' => 4,
            'grid_size' => 4,
            'turn_count' => 0,
            'current_player_id' => null,
            'card_positions' => [],

        ]);
        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => auth()->id(),
            'score' => 0,
            'turn_order' => 1,
            'is_active' => true,
        ]);

        $game->update([
            'current_player_id' => auth()->id(),
        ]);

        $this->gameName = '';

        GameUpdated::dispatch($game->id, 'player_joined');
    }

    #[On('echo:games,GameUpdated')]
    public function gameUpdated()
    {
        $this->loadGames();
    }

    public function joinGame($id)
    {
        $game = Game::findOrFail($id);
        if (! $game or $game->status !== 'new') {
            session()->flash('error', 'Game not available');

            return;
        }
        if ($game->players->count() == $game->max_players) {
            session()->flash('error', 'Game is full');

            return;
        }
        if ($game->players()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('game.show', $game);
        }
        $turnOrder = $game->players->max('turn_order') + 1;

        GamePlayer::create([
            'game_id' => $game->id,
            'user_id' => auth()->id(),
            'score' => 0,
            'turn_order' => $turnOrder,
            'is_active' => true,
        ]);

        GameUpdated::dispatch($game->id, 'player_joined');

        return redirect()->route('game.show', $game);

    }

    public function render()
    {
        return view('livewire.game-lobby');
    }
}
