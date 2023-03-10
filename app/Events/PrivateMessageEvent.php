<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrivateMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $toUserId, public string $message)
    {
        //

    }

    protected function buildChannelName()
    {
        $channelString = '';
        if (auth()->id() > $this->toUserId) $channelString .= auth()->id() . '.' . $this->toUserId;
        else $channelString .= $this->toUserId . "." . auth()->id();
        return $channelString;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            new PresenceChannel('private.message.' . $this->buildChannelName()),
        ];
    }

    public function broadcastAs()
    {
        return 'privateChat';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'user' => collect(auth()->user())->only('id', 'name', 'email'),
            'date' => Carbon::now()->format('Y-m-d H:m:s'),
            'isRead' => false,

        ];
    }
}
