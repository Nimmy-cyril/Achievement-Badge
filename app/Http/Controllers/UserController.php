<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Lesson;
use Auth;

class UserController extends Controller
{
    
    //when a user watches a lesson this event is triggered
    public function watchLesson(Request $request, $lessonId)
    {
        $user = Auth::user();
        $lesson = Lesson::find($lessonId);

        // Check if the user has already watched this lesson
        if (!$user->hasWatchedLesson($lesson)) {
            // Update the lessons_user pivot table
            $user->watchLesson($lesson);

            // Check if the user unlocked any achievements
            $unlockedAchievements = $user->unlockAchievementsForWatchingLessons();

            foreach ($unlockedAchievements as $achievement) {
                event(new AchievementUnlocked($achievement->name, $user));
            }
            // Check if the user unlocked a new badge
            $newBadge = $user->unlockBadge();

            if ($newBadge) {
                event(new BadgeUnlocked($newBadge->name, $user));
            }
        }

        // Redirect accordingly
    }

    //when a user comment this event is triggered
    public function postComment(Request $request)
    {
        $user = Auth::user();
        $comment = new Comment();
        $comment->body = $request->input('body');
        $comment->user_id = $user->id;
        $comment->save();

        // Check if the user unlocked any achievements
        $unlockedAchievements = $user->unlockAchievementsForCommentsWritten();
        foreach ($unlockedAchievements as $achievement) {
            event(new AchievementUnlocked($achievement->name, $user));
        }
        // Check if the user unlocked a new badge
        $newBadge = $user->unlockBadge();
        if ($newBadge) {
            event(new BadgeUnlocked($newBadge->name, $user));
        }
         // Redirect accordingly
    }

}
