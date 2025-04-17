<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function storeComment(Request $request)
    {
   
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
            'parent_comment_id' => 'nullable',
        ]);

        $userId = Auth::id();

       
        $comment = PostComment::create([
            'user_id' => $userId,
            'post_id' => $request->post_id,
            'parent_comment_id' => $request->parent_comment_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Comment added successfully!',
            'comment' => $comment,
        ]);
    }
}
