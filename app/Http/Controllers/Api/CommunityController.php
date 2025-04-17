<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Community;

class CommunityController extends Controller
{


    public function getCommunities($id)
    {

        $communities = Community::where('user_id', $id)->get();

        $communities->transform(function ($community) {
            if ($community->banner_image) {
                $community->banner_image = url($community->banner_image);
            }
            return $community;
        });

        return response()->json([
            'message' => 'List of communities.',
            'community' => $communities,
        ], 200);
    }


    public function addNewCommunity(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cat_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|string',  // Accept base64 string
            'members' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ]);

        $data = $request->only(['user_id', 'cat_id', 'name', 'description', 'members', 'status']);

        if ($request->has('banner_image')) {
            $imageData = $request->banner_image;

            $image = base64_decode($imageData);
            $extension = strpos($imageData, 'data:image/jpeg') === 0 ? 'jpeg' : 'png';
            $imageName = time() . '_' . uniqid() . '.' . $extension;
            $destinationPath = public_path('media/community');

            // Save the image file
            file_put_contents($destinationPath . '/' . $imageName, $image);
            $data['banner_image'] = 'media/community/' . $imageName;
        }

        $community = Community::create($data);

        return response()->json([
            'message' => 'Community created successfully.',
            'community' => $community,
        ], 201);
    }

    public function joinCommunity(Request $request, $communityId)
    {
        $user = auth()->user();
        $community = Community::findOrFail($communityId);

        if ($community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already a member'], 400);
        }

        $community->members()->attach($user->id);

        return response()->json(['message' => 'Joined successfully']);
    }

    public function leaveCommunity(Request $request, $communityId)
    {
        $user = auth()->user();
        $community = Community::findOrFail($communityId);

        if (!$community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Not a member'], 400);
        }

        $community->members()->detach($user->id);

        return response()->json(['message' => 'Left the community']);
    }

    public function membersCount($id)
    {
        $user = auth()->user();
        $community = Community::withCount('members')->findOrFail($id);

        $isMember = $community->members()->where('user_id', $user->id)->exists();

        return response()->json([
            'id' => $community->id,
            'name' => $community->name,
            'members_count' => $community->members_count,
            'is_member' => $isMember, // ✅ Add this to determine if the user is already a member
        ]);
    }
}
