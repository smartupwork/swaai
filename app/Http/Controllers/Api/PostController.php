<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\PostShare;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\Community;

class PostController extends Controller
{
    public function getPosts($id)
    {
        $community = Community::find($id);
        $posts = Post::where('community_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                $user = User::find($post->user_id);
                $userMeta = UserMeta::where('user_id', $post->user_id)->first();
                $allComments = PostComment::where('post_id', $post->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $comments = $allComments->filter(fn($comment) => $comment->parent_comment_id === null)
                    ->map(function ($comment) use ($allComments) {
                        $commentUser = User::find($comment->user_id);
                        $replies = $allComments->filter(fn($reply) => $reply->parent_comment_id === $comment->id)
                            ->map(function ($reply) {
                                $replyUser = User::find($reply->user_id);
                                return [
                                    'id' => $reply->id,
                                    'comment' => $reply->content,
                                    'commented_by' => $replyUser ? $replyUser->first_name . ' ' . $replyUser->last_name : 'Anonymous',
                                    'comment_posted_time' => $reply->created_at,
                                ];
                            })->values();
                        return [
                            'id' => $comment->id,
                            'comment' => $comment->content,
                            'commented_by' => $commentUser ? $commentUser->first_name . ' ' . $commentUser->last_name : 'Anonymous',
                            'comment_posted_time' => $comment->created_at,
                            'replies' => $replies,
                        ];
                    })->values();
                $shares = PostShare::where('post_id', $post->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($share) {
                        $shareUser = User::find($share->user_id);
                        return [
                            'shared_at' => $share->created_at,
                            'shared_by' => $shareUser ? $shareUser->first_name . ' ' . $shareUser->last_name : 'Anonymous',
                        ];
                    });
                return [
                    'post' => $post,
                    'posted_by' => $user ? $user->first_name . ' ' . $user->last_name : 'Anonymous',
                    'state' => $userMeta?->state,
                    'city' => $userMeta?->city,
                    'reactions_count' => PostReaction::where('post_id', $post->id)->count(),
                    'comments' => $comments,
                    'shares' => $shares,
                ];
            });
        return response()->json([
            'message' => 'Posts for Community',
            'community_name' => $community?->name ?? 'Unknown Community',
            'posts' => $posts,
        ], 200);
    }
    public function getMyPosts($id)
    {
        $user = User::find($id);
        $userMeta = UserMeta::where('user_id', $id)->first();
        $community = Community::find($id);
        $posts = Post::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) {
                $comments = PostComment::where('post_id', $post->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($comment) {
                        $commentUser = User::find($comment->user_id);
                        $replies = PostComment::where('parent_comment_id', $comment->id)
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(function ($reply) {
                                $replyUser = User::find($reply->user_id);
                                return [
                                    'id' => $reply->id,
                                    'comment' => $reply->content,
                                    'commented_by' => $replyUser ? $replyUser->first_name . ' ' . $replyUser->last_name : 'Anonymous',
                                    'comment_posted_time' => $reply->created_at,
                                ];
                            });
                        return [
                            'id' => $comment->id,
                            'comment' => $comment->content,
                            'commented_by' => $commentUser ? $commentUser->first_name . ' ' . $commentUser->last_name : 'Anonymous',
                            'comment_posted_time' => $comment->created_at,
                            'replies' => $replies,
                        ];
                    });
                $shares = PostShare::where('post_id', $post->id)->get()->map(function ($share) {
                    $shareUser = User::find($share->user_id);
                    return [
                        'shared_at' => $share->created_at,
                        'shared_by' => $shareUser ? $shareUser->first_name . ' ' . $shareUser->last_name : 'Anonymous',
                    ];
                });
                return [
                    'post' => $post,
                    'reactions_count' => PostReaction::where('post_id', $post->id)->count(),
                    'comments_count' => $comments->count(),
                    'shares_count' => $shares->count(),
                    'comments' => $comments,
                    'shares' => $shares,
                ];
            });
        return response()->json([
            'message' => 'User Posts',
            'user_full_name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User',
            'state' => $userMeta ? $userMeta->state : null,
            'city' => $userMeta ? $userMeta->city : null,
            'community_name' => $community ? $community->name : null,
            'posts' => $posts,
        ], 200);
    }
    public function addPosts(Request $request)
    {
        $validated = $request->validate([
            'community_id' => 'required|exists:communities,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
        ]);
        $post = Post::create([
            'community_id' => $validated['community_id'],
            'user_id' => $validated['user_id'],
            'content' => $validated['content'],
        ]);
        $user = User::find($validated['user_id']);
        $userMeta = UserMeta::where('user_id', $validated['user_id'])->first();
        $community = Community::find($validated['community_id']);
        $userFullName = $user->first_name . ' ' . $user->last_name;
        $responseData = [
            'message' => 'Post created successfully',
            'post' => $post,
            'user_full_name' => $userFullName,
            'state' => $userMeta ? $userMeta->state : null,
            'city' => $userMeta ? $userMeta->city : null,
            'community_name' => $community ? $community->name : null,
        ];
        return response()->json($responseData, 201);
    }
}
