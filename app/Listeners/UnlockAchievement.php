<?php

namespace App\Listeners;

use App\Events\CommentWritten;
use App\Models\Achievement;
use App\Models\Badge;
use App\Options\AchievementOptions;
use App\Options\BadgeOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class UnlockAchievement
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param CommentWritten $event
     * @return void
     */
    public function handle(CommentWritten $event)
    {
        if ($event->comment->user->comments()->count() === 1){
            //Unlock the first comment written achievement for user through user comments mapping
            $event->comment->user
                ->achievements()
                ->attach(Achievement::where('name', AchievementOptions::FIRST_COMMENT_WRITTEN)->first()->id);

            //Unlock the first badge for user through using user badges  mapping
            $event->comment->user
                ->badges()
                ->attach(Badge::where('name', BadgeOption::STARTER_BADGE)->first()->id);
        }

        if ($event->comment->user->comments()->count() === 5){
            //Unlock the fifth comment written achievement for user through user comments mapping
            $event->comment->user
                ->achievements()
                ->attach(Achievement::where('name', AchievementOptions::FIFTH_COMMENT_WRITTEN)->first()->id);

            $event->comment->user->unLockIntermediateBadge();
        }
    }
}
