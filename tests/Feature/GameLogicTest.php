<?php

declare(strict_types=1);

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('initializes the game with the correct number of cards and sets status to playing', function (): void {
    $host = User::factory()->create();

    $game = Game::create([
        'name' => 'Test Game',
        'status' => 'new',
        'max_players' => 4,
        'grid_size' => 4, // 4x4 grid = 16 cards = 8 pairs
        'turn_count' => 0,
        'current_player_id' => $host->id,
        'card_positions' => [],
    ]);

    GamePlayer::create([
        'game_id' => $game->id,
        'user_id' => $host->id,
        'score' => 0,
        'turn_order' => 1,
        'is_active' => true,
    ]);

    $game->initializeGame();
    $game->refresh();

    expect($game->status)->toBe('playing')
        ->and($game->cards()->count())->toBe(16)
        ->and($game->cards()->where('is_flipped', true)->count())->toBe(0)
        ->and($game->cards()->where('is_matched', true)->count())->toBe(0);
});

it('rotates to the next player when nextPlayer is called', function (): void {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $game = Game::create([
        'name' => 'Rotation Test',
        'status' => 'new',
        'max_players' => 4,
        'grid_size' => 2, // small grid for simplicity
        'turn_count' => 0,
        'current_player_id' => $u1->id,
        'card_positions' => [],
    ]);

    GamePlayer::create(['game_id' => $game->id, 'user_id' => $u1->id, 'score' => 0, 'turn_order' => 1, 'is_active' => true]);
    GamePlayer::create(['game_id' => $game->id, 'user_id' => $u2->id, 'score' => 0, 'turn_order' => 2, 'is_active' => true]);

    // call nextPlayer should set current to u2
    $game->nextPlayer();
    $game->refresh();

    expect($game->current_player_id)->toBe($u2->id)
        ->and($game->turn_count)->toBe(1);

    // call again rotates back to u1
    $game->nextPlayer();
    $game->refresh();

    expect($game->current_player_id)->toBe($u1->id)
        ->and($game->turn_count)->toBe(2);
});
