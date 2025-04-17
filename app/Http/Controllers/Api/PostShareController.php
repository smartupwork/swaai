<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostShare;
use Illuminate\Support\Facades\Auth;

class PostShareController extends Controller
{
    public function storeShare(Request $request)
    {
        
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);
        
        $userId = Auth::id();

        
        $share = PostShare::create([
            'user_id' => $userId,
            'post_id' => $request->post_id,
            'share_to' => $request->share_to,
        ]);

        return response()->json([
            'message' => 'Post shared successfully!',
            'share' => $share,
        ]);
    }
}
