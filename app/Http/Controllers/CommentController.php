<?php

namespace App\Http\Controllers;

use App\Events\CommentWritten;
use App\Http\Requests\StoreComment;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param StoreComment $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreComment $request, User $user): \Illuminate\Http\JsonResponse
    {//dd($user);
        $comment = Comment::create(['body' => $request->body, 'user_id' => $user->id]);

        CommentWritten::dispatch($comment);

        return response()->json(['message' => 'Comment posted successfully'], 201);
    }
}
