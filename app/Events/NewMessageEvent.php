<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message,
        public readonly User    $sender
    ) {}

    /**
     * Canal privé du destinataire : chat.{receiver_id}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    /**
     * Nom de l'événement côté JS : .new-message
     */
    public function broadcastAs(): string
    {
        return 'new-message';
    }

    /**
     * Données envoyées au frontend
     */
    public function broadcastWith(): array
    {
        return [
            'id'           => $this->message->id,
            'body'         => $this->message->body,
            'sender_id'    => $this->sender->id,
            'sender_name'  => $this->sender->name,
            'sender_photo' => $this->sender->photo
                                ? asset('storage/' . $this->sender->photo)
                                : null,
            'created_at'   => $this->message->created_at->toISOString(),
        ];
    }
}
