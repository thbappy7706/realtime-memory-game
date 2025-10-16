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


    public function players():HasMany
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
    public function currentPlayer():BelongsTo
    {
        return $this->belongsTo(User::class, 'current_player_id');
    }
}
