<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{
    public function index(Request $request, $user)
    {
        //$userId = $user;
        $userId=1;
        $unlockedAchievements = $this->unlockedAchievements($userId);
        $nextAvailableAchievements = $this->getNextAvailableAchievements($userId);
        $currentBadge = $this->getCurrentBadge($userId);
        $nextBadge = $this->getNextBadge($userId);
        $remainingToUnlockNextBadge = $this->getRemainingToUnlockNextBadge($userId);
        return [
            'unlocked_achievements' => $unlockedAchievements,
            'next_available_achievements' => $nextAvailableAchievements,
            'current_badge' => $currentBadge,
            'next_badge' => $nextBadge,
            'remaining_to_unlock_next_badge' => $remainingToUnlockNextBadge,
        ];
    }


        private function unlockedAchievements($userId){
            $user = new User(); 
            $unlockedAchievements = $user->unlockedAchievements($userId);
            return $unlockedAchievements;
        }

    
        private function getNextAvailableAchievements($userid)
        {   
            $user = new User(); 
            $nextAvailableAchievements= $user->getNextAvailableAchievements($userid);
            return $nextAvailableAchievements;
        
        }



        private function getCurrentBadge($userid)
        {    
            $user = new User(); 
            $unlockedAchievementsCount = count($this->unlockedAchievements($userid));
            if ($unlockedAchievementsCount >= 10) {
                return 'Master';
            } elseif ($unlockedAchievementsCount >= 8) {
                return 'Advanced';
            } elseif ($unlockedAchievementsCount >= 4) {
                return 'Intermediate';
            } else {
                return 'Beginner';
            }
        }

        private function getNextBadge($userid)
        {   
            $user = new User(); 
            $unlockedAchievementsCount = count($this->unlockedAchievements($userid));
            $NextBage = $user->getNextBadge($unlockedAchievementsCount);
            return $NextBage;
        }

        private function getRemainingToUnlockNextBadge($userid)
        {
            $unlockedAchievementsCount = count($this->unlockedAchievements($userid));
            $nextBadge = $this->getNextBadge($userid);

            if (!$nextBadge) {
                return 0; // User has reached the highest badge.
            }
            $requiredAchievements = Badge::where('name', $nextBadge)->first()->required_achievements;
            return max(0, $requiredAchievements - $unlockedAchievementsCount);
        }

}
