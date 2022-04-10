<?php

namespace App\Listeners;

use App\Events\CommentWritten;
use App\Events\LessonWatched;
use App\Models\Achievement;
use App\Models\Badge;
use App\Options\AchievementOptions;
use App\Options\BadgeOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class RegisterWatchedLesson
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
     * @param LessonWatched $event
     * @return void
     */
    public function handle(LessonWatched $event)
    {
        $event->user->lessons()->attach($event->lesson->id, ['watched' => true]);

        if ($event->user->lessons()->count()  === 1){
            $event->user->achievements()
                ->attach(Achievement::where('name', AchievementOptions::FIRST_LESSON_WATCHED)->first()->id);
        }

        if ($event->user->lessons()->count()  === 3){
            $event->user->achievements()
                ->attach(Achievement::where('name', AchievementOptions::THIRD_LESSON_WATCHED)->first()->id);
        }

        if($event->user->lessons()->count()  === 5){
            $event->user->achievements()
                ->attach(Achievement::where('name', AchievementOptions::FIVE_LESSON_WATCHED)->first()->id);

            $event->user->unLockIntermediateBadge();
        }

    }
}
