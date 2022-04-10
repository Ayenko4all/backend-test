<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(User $user): \Illuminate\Http\JsonResponse
    {
        $badge = $user->badges()->orderByDesc('id')->first();

        return response()->json([
            'unlocked_achievements' => $user->achievements()->select(['name'])->get(),
            'next_available_achievements' => $user->availableAchievements(),
            'current_badge' => $badge ? $badge->name : null,
            'next_badge' => $user->nextAvailableBadge(),
            'remaing_to_unlock_next_badge' => $user->remainingToUnlockNextBadge()
        ], 200);
    }
}
