<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $card;
    public $boardId;
    public $oldColumnId;
    public $newColumnId;
    public $newPosition;

    /**
     * Create a new event instance.
     */
    public function __construct(Card $card, $oldColumnId, $newColumnId, $newPosition)
    {
        $this->card = $card;
        $this->boardId = $card->board_id;
        $this->oldColumnId = $oldColumnId;
        $this->newColumnId = $newColumnId;
        $this->newPosition = $newPosition;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('board.' . $this->boardId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'card' => $this->card->load(['user', 'column']),
            'oldColumnId' => $this->oldColumnId,
            'newColumnId' => $this->newColumnId,
            'newPosition' => $this->newPosition,
            'type' => 'card_moved'
        ];
    }
}
