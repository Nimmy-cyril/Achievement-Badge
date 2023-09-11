<?php

namespace App\Listeners;

use App\Events\LessonWatched;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LessonWatchedListener
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
    public function handle(LessonWatched  $event): void
    {
        $user = $event->user; 
        $lesson = $event->lesson; 
        //To unlock achievements for watching lessons
        $unlockedAchievements = $user->unlockAchievementsForWatchingLessons(); 
        // Check if the user unlocked a new badge
        $newBadge = $user->unlockBadge();
        if ($newBadge) {
            $badge_name=$newBadge->name;
            event(new BadgeUnlocked($badge_name, $user));
        }
    }
    
}
