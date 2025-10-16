<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
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
        $this->data['played_id'] = auth()->id();
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
            'data' => array_merge($this->data, ['played_id' => $this->data['played_id'] ?? auth()->id()]),
        ];
    }
}
