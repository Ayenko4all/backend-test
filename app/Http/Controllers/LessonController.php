<?php

namespace App\Http\Controllers;

use App\Events\LessonWatched;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __invoke(Lesson $lesson, User $user): \Illuminate\Http\JsonResponse
    {
        LessonWatched::dispatch($lesson, $user);

        return response()->json(['message' => "You have successfully finished watching {$lesson->title}"], 200);
    }
}
