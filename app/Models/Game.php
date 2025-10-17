<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $guarded = [];

    protected $casts = [
        'card_positions' => 'array',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    /**
     * A game has many cards.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(GameCard::class);
    }

    /**
     * The current player (if any).
     */
    public function currentPlayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_player_id');
    }

    public function initializeGame()
    {
        $totalCards = $this->grid_size * $this->grid_size;
        $pairs = $totalCards / 2;
        $emojis = [
            '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼',
            '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🦄',
            '🐙', '🦋', '🦂', '🦀', '🐠', '🐳', '🦕', '🦖',
            '🌺', '🌸', '🌼', '🌻', '🌹', '🥀', '🌷', '💐',
            '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓',
            '⚽️', '🏀', '🏈', '⚾️', '🎾', '🏐', '🏉', '🎱',
            '🚗', '🚕', '🚙', '🚌', '🚎', '🏎', '🚓', '🚑',
            '🌈', '⭐️', '🌙', '⚡️', '☀️', '🌤', '🌍', '🎵',
        ];
        $cardValues = [];
        for ($i = 0; $i < $pairs; $i++) {
            $emoji = $emojis[$i % count($emojis)];
            $cardValues[] = $emoji;
            $cardValues[] = $emoji;
        }
        shuffle($cardValues);

        foreach ($cardValues as $position => $value) {
            $this->cards()->create([
                'position' => $position,
                'card_value' => $value,
                'is_flipped' => false,
                'is_matched' => false,
            ]);
        }

        $this->update(['status' => 'playing']);
    }

    /**
     * Move to the next player's turn
     */
    public function nextPlayer()
    {
        $players = $this->players()->orderBy('turn_order')->get();

        if ($players->isEmpty()) {
            return;
        }

        // Find current player index
        $currentIndex = $players->search(function ($player) {
            return $player->user_id == $this->current_player_id;
        });

        // Move to next player (circular)
        $nextIndex = ($currentIndex + 1) % $players->count();
        $nextPlayer = $players[$nextIndex];

        $this->update([
            'current_player_id' => $nextPlayer->user_id,
            'turn_count' => $this->turn_count + 1,
        ]);

        return $nextPlayer;
    }

    /**
     * Check if the game is complete (all cards matched)
     */
    public function isGameComplete()
    {
        return $this->cards()->where('is_matched', false)->count() === 0;
    }

    /**
     * Get the winning player(s)
     */
    public function getWinners()
    {
        $maxScore = $this->players()->max('score');

        return $this->players()->where('score', $maxScore)->with('user')->get();
    }
}
