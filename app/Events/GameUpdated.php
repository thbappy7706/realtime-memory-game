<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $gameId, public string $type, public array $data = [])
    {
        // Ensure the broadcasting payload always includes the acting player's ID
        $this->data['player_id'] = $this->data['player_id'] ?? auth()->id();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('games'),
            new Channel("game.{$this->gameId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->gameId,
            'type' => $this->type,
            'data' => array_merge($this->data, ['player_id' => $this->data['player_id'] ?? auth()->id()]),
        ];
    }
}
