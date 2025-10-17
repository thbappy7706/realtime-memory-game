<?php

namespace App\Livewire;

use App\Events\GameUpdated;
use App\Models\Game;
use Livewire\Attributes\On;
use Livewire\Component;

class GameRoom extends Component
{
    public Game $game;

    public $cards = [];

    public $gameMessage;

    public $isProcessing = false;

    public $flippedCards = [];

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->loadCards();
    }

    public function loadCards()
    {
        $this->cards = $this->game->cards()
            ->orderBy('position')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'position' => $card->position,
                    'value' => $card->card_value,
                    'is_flipped' => $card->is_flipped,
                    'is_matched' => $card->is_matched,
                    'show_value' => $card->is_flipped || $card->is_matched,
                ];
            })->toArray();
    }

    public function startGame()
    {
        if ($this->game->status !== 'new') {
            return;
        }

        $this->game->initializeGame();
        $this->loadCards();
        $this->gameMessage = 'Game started! '.$this->game->currentPlayer->name."'s turn.";
        GameUpdated::dispatch($this->game->id, 'game_started');
    }

    #[On('echo:game.{game.id},GameUpdated')]
    public function gameUpdated($data)
    {
        $this->game->refresh();
        $this->loadCards();

        switch ($data['type']) {
            case 'game_started':
                $this->gameMessage = 'Game started! '.$this->game->currentPlayer->name."'s turn.";
                break;
            case 'card_flipped':
                if ($data['data']['player_id'] !== auth()->id()) {
                    $this->gameMessage = $this->game->currentPlayer->name.' flipped a card.';
                }
                break;
            case 'turn_changed':
                $this->gameMessage = $this->game->currentPlayer->name.' started turn.';
                break;

            case 'both_cards_flipped':
                if ($data['data']['player_id'] !== auth()->id()) {
                    $this->gameMessage = $this->game->currentPlayer->name.' flipped a card.';
                }
                break;

            case 'match_found':
                if ($data['data']['player_id'] !== auth()->id()) {
                    $this->gameMessage = $this->game->currentPlayer->name."'s match found!";
                }
                break;

            case 'no_match':
                if ($data['data']['player_id'] !== auth()->id()) {
                    $this->gameMessage = $this->game->currentPlayer->name.'\'s tried '.$data['data']['card_values'][0].'!';
                }
                break;
            case 'game_finished':
                $this->gameMessage = 'Game Over! '.$this->game->currentPlayer->name.' won!';
                break;
        }
    }

    public function flipCard($cardId)
    {
        if ($this->isProcessing || $this->game->currentPlayer->id != auth()->id() || $this->game->status !== 'playing') {

            return;
        }

        $card = $this->game->cards()->find($cardId);
        if (! $card || $card->is_flipped || $card->is_matched || count($this->flippedCards) >= 2) {

            return;
        }

        $card->update(['is_flipped' => true]);
        $this->flippedCards[] = $cardId;
        $this->loadCards();

        if (count($this->flippedCards) === 1) {

            GameUpdated::dispatch($this->game->id, 'card_flipped', ['card_id' => $cardId, 'player_id' => auth()->id()]);

            return;
        } elseif (count($this->flippedCards) === 2) {
            $this->isProcessing = true;
            GameUpdated::dispatch($this->game->id, 'both_cards_flipped', [
                'card_ids' => $this->flippedCards, 'player_id' => auth()->id(), 'first_card_id' => $this->flippedCards[0], 'second_card_id' => $this->flippedCards[1],
            ]);
            $this->dispatch('cards-flipped');
        }

    }

    public function checkMatch(): void
    {
        if (count($this->flippedCards) !== 2) {
            return;
        }

        $card1 = $this->game->cards()->find($this->flippedCards[0]);
        $card2 = $this->game->cards()->find($this->flippedCards[1]);

        if ($card1->card_value === $card2->card_value) {
            $card1->update(['is_matched' => true, 'matched_by_user_id' => auth()->id()]);
            $card2->update(['is_matched' => true, 'matched_by_user_id' => auth()->id()]);
            $player = $this->game->players()->where('user_id', auth()->id())->first();
            $player->increment('score');
            $this->gameMessage = auth()->user()->name.'\'s ('.$card1->card_value.')'.' match found!';
            GameUpdated::dispatch($this->game->id, 'match_found', ['player_id' => auth()->id(), 'card_ids' => $this->flippedCards]);

            if ($this->game->isGameComplete()) {
                $this->game->update(['status' => 'finished']);
                $winner = $this->game->players()->where('score', $this->game->players()->max('score'))->first();
                $this->gameMessage = 'Game Over! '.$winner->user->name.' won!';
                GameUpdated::dispatch($this->game->id, 'game_finished');
            }

            $this->flippedCards = [];
            $this->isProcessing = false;
            $this->loadCards();
        } else {
            $this->gameMessage = 'No match - '.$this->game->currentPlayer->name.'\'s tried '.$card1->card_value.'!';
            GameUpdated::dispatch($this->game->id, 'no_match', ['player_id' => auth()->id(), 'card_ids' => $this->flippedCards, 'card_values' => [$card1->card_value, $card2->card_value]]);

            // Move to next player when no match
            $this->game->nextPlayer();
            $this->dispatch('flip-cards-back')->self();
        }
    }

    #[On('flip-cards-back')]
    public function flipCardsBack()
    {
        if (count($this->flippedCards) === 2) {
            $card1 = $this->game->cards()->find($this->flippedCards[0]);
            $card2 = $this->game->cards()->find($this->flippedCards[1]);
            $card1?->update(['is_flipped' => false]);
            $card2?->update(['is_flipped' => false]);

            $this->flippedCards = [];
            $this->isProcessing = false;

            $this->gameMessage = $this->game->currentPlayer->name.'\'s turn.';
            GameUpdated::dispatch($this->game->id, 'turn_changed');
            $this->loadCards();
        }
    }

    public function render()
    {
        return view('livewire.game-room', [
            'players' => $this->game
                ->players()
                ->with('user')
                ->orderBy('turn_order')
                ->get(),
        ]);
    }
}
