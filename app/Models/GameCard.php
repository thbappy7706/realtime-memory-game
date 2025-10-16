<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameCard extends Model
{
    protected $guarded = [];

    protected $casts = [
      'is_flipped' => 'boolean',
      'is_matched' => 'boolean',
    ];


    /**
     * Each card belongs to a game.
     */
    public function game():BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * The user who matched this card (optional).
     */
    public function matchedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by_user_id');
    }
}
