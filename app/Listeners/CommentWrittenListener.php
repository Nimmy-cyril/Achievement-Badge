<?php

namespace App\Listeners;

use App\Events\CommentWritten;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentWrittenListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CommentWritten  $event): void
    {
        $user = $event->user; 
        $unlockedAchievements = $user->unlockAchievementsForCommentsWritten();
        // Check if the user unlocked a new badge
        $newBadge = $user->unlockBadge();
        if ($newBadge) {
            $badge_name=$newBadge->name;
            event(new BadgeUnlocked($badge_name, $user));
        }
    }
}
