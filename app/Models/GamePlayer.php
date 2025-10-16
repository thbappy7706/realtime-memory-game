<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayer extends Model
{
    protected $guarded = [];

    public function game():BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Each player is associated with a user.
     */
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
