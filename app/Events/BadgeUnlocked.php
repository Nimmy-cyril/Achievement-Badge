<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeUnlocked
{
    public $badge_name;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param string $badge_name
     * @param User $user
     */
    public function __construct($badge_name, User $user)
    {
        $this->badge_name = $badge_name;
        $this->user = $user;
    }

}
