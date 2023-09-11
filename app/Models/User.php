<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Achievement;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function lessons()
        {
            return $this->belongsToMany(Lesson::class, 'lessons_user')->withPivot('watched');
        }

    public function achievements() {
        return $this->belongsToMany(Achievement::class, 'user_achievements');
    }

    public function badges() {
        return $this->belongsToMany(Badge::class, 'user_badges');

    }

    public function unlockedAchievements($userid){
        $unlockedAchievements = DB::table('user_achievements')
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->select('achievements.name')
            ->where('user_achievements.user_id', $userid)
            ->get()
            ->pluck('name')
            ->toArray();

        return $unlockedAchievements;
    }

    public function getNextAvailableAchievements($userid){
        //unlocked achievements by name
        $unlockedAchievements = DB::table('user_achievements')
        ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
        ->select('achievements.name')
        ->where('user_achievements.user_id', $userid)
        ->get()
        ->pluck('name')
        ->toArray();

        // created separate arrays for different achievement groups
        $lessonsWatchedAchievements = [
            'First Lesson Watched',
            '5 Lessons Watched',
            '10 Lessons Watched',
            '25 Lessons Watched',
            '50 Lessons Watched',
        ];

        $commentsWrittenAchievements = [
            'First Comment Written',
            '3 Comments Written',
            '5 Comments Written',
            '10 Comment Written',
            '20 Comment Written',
        ];

        $nextAvailableLessonAchievement = current(array_diff($lessonsWatchedAchievements, $unlockedAchievements));
        $nextAvailableCommentAchievement = current(array_diff($commentsWrittenAchievements, $unlockedAchievements));

        $nextAvailableAchievements = [
            'lesson' => $nextAvailableLessonAchievement,
            'comment' => $nextAvailableCommentAchievement,
        ];

        return $nextAvailableAchievements;
    }


    public function getNextBadge($unlockedAchievementsCount){
        //next badge based on the number of achievements
        $nextBadge = Badge::where('required_achievements', '>', $unlockedAchievementsCount)
        ->orderBy('required_achievements', 'asc')
        ->first();
        return $nextBadge ? $nextBadge->name : null;

    }


    public function unlockAchievementsForWatchingLessons() {
        $userId = auth()->user()->id;
        //To get the number of lessons watched by a user
        $watchedLessonCount = $this->lessons()
        ->where('lessons_user.user_id', $userId)
        ->where('lessons_user.watched', true)
        ->count();

        // Determine which lesson achievements to unlock based on user's watched lessons
            $achievementsToUnlock = [
                'First Lesson Watched' => 1,
                '5 Lessons Watched' => 5,
                '10 Lessons Watched' => 10,
                '25 Lessons Watched' => 25,
                '50 Lessons Watched' => 50,
            ];

            foreach ($achievementsToUnlock as $achievementName => $requiredWatched) {
                if ($watchedLessonCount >= $requiredWatched) {
                    $this->unlockAchievement($achievementName);
                    // Dispatch AchievementUnlocked event
                    event(new AchievementUnlocked($achievementName, $this));
                }
            }
    }

    public function unlockAchievement(string $achievementName){
        // Find the achievement by name
        $achievement = Achievement::where('name', $achievementName)->first();
        if ($achievement) {
            // Check if the user already has this achievement
            if (!$this->achievements()->where('achievement_id', $achievement->id)->exists()) {
                // Insert the achievement for the user
                $this->achievements()->attach($achievement->id);
            }

        }
    }

    public function unlockAchievementsForCommentsWritten() {
       // The number of comments written by the user
        $commentsWrittenCount = $this->comments()->count();

        //The achievements and their corresponding comment counts required
        $achievementsToUnlock = [
            'First Comment Written' => 1,
            '3 Comments Written' => 3,
            '5 Comments Written' => 5,
            '10 Comment Written' => 10,
            '20 Comment Written' => 20,
        ];

        foreach ($achievementsToUnlock as $achievementName => $requiredComments) {
            if ($commentsWrittenCount >= $requiredComments) {
                $this->unlockAchievement($achievementName);
                event(new AchievementUnlocked($achievementName, $this));
            }
        }
    }
    
    public function unlockBadge() {
        $totalAchievements = $this->achievements()->count();
        $nextBadge = Badge::where(function ($query) use ($totalAchievements) {
            if ($totalAchievements >= 10) {
                $query->where('required_achievements', 10);
            } elseif ($totalAchievements >= 8) {
                $query->where('required_achievements', 8);
            } elseif ($totalAchievements >= 4) {
                $query->where('required_achievements', 4);
            } elseif ($totalAchievements >= 0) {
                $query->where('required_achievements', 0);
            }
        })->orderBy('required_achievements', 'desc')->first();

        if ($nextBadge && !$this->badges->contains($nextBadge)) {
            // Attach the new badge to the user
            $this->badges()->attach($nextBadge->id);
            return $nextBadge;
    }

            return null;
    }
}