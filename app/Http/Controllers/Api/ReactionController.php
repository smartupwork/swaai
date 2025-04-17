<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostReaction;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function storeReaction(Request $request)
    {
        
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        
        $userId = Auth::id();

        $reaction = PostReaction::where('user_id', $userId)
            ->where('post_id', $request->post_id)
            ->first();

        if ($reaction) {          
            $reaction->delete();
            return response()->json(['message' => 'Reaction removed successfully!']);
        } else {
        
            PostReaction::create([
                'user_id' => $userId,
                'post_id' => $request->post_id,
                'reaction' => 'love',
            ]);
            return response()->json(['message' => 'Reaction saved successfully!']);
        }
    }

    
}
