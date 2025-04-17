<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\Business;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function getProfile($id)
    {
        $business = Business::where('user_id', $id)->first();
        $user = User::where('id', $id)->first();
        if (!$business || !$user) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $baseUrl2 = request()->getSchemeAndHttpHost();
        $profileImageUrl = $baseUrl2 . '/' . $user->profile_image;
        $data = [
            'business_id' => $business->id,
            'business_name' => $business->name,
            'profile_image' => $profileImageUrl,
            'user_id' => $user->id,
            'email' => $user->email,
            'password' => $user->password,
            'address' => $user->address,
        ];
        return response()->json([
            'message' => 'Profile data retrieved successfully',
            'data' => $data,
        ], 200);
    }


    public function updateProfile(Request $request)
    {

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'business_id' => 'required|exists:businesses,id',
            'business_name' => 'nullable|string',
            'profile_image' => 'nullable|string',  // Handle base64 profile image
            'email' => 'nullable|email|unique:users,email,' . $request->user_id,
            'password' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        // Find user by user_id
        $user = User::find($request->user_id);

        if ($user) {

            // Update email if provided and not empty
            if ($request->has('email') && $request->email !== '') {

                $user->email = $request->email;
            }

            // Update password if provided
            if ($request->has('password') && $request->password !== '') {
                $user->password = Hash::make($request->password);
            }

            // Update address if provided
            if ($request->has('address') && $request->address !== '') {
                $user->address = $request->address;
            }

            // Update profile image if provided
            if ($request->has('profile_image') && $request->profile_image !== '') {
                $oldImage = $user->profile_image;
                $imageData = $request->profile_image;
                $imageName = 'profile_' . time() . '.png';
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $imageData = base64_decode($imageData);
                $imagePath = public_path('user/' . $imageName);
                file_put_contents($imagePath, $imageData);
                $user->profile_image = 'user/' . $imageName;

                // Delete the old image if it's not the same as the new one
                if ($oldImage && $oldImage !== $user->profile_image) {
                    $oldImagePath = public_path($oldImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
            }

            // Save the updated user information
            $user->save();
        }

        // Find business by business_id
        $business = Business::find($request->business_id);
        if ($business) {
            // Update business name if provided
            if ($request->has('business_name') && $request->business_name !== '') {
                $business->name = $request->business_name;
            }

            // Save the updated business information
            $business->save();
        }

        // Return response after profile and business update
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
            'business' => $business
        ]);
    }

    public function updateBusinessAddress($id)
    {

        $validatedData = request()->validate([
            'business_address' => 'required|string|max:255',
        ]);


        $business = Business::find($id);

        if ($business) {
            $business->business_address = $validatedData['business_address'];
            $business->save();

            return response()->json(['message' => 'Business address updated successfully!']);
        } else {
            return response()->json(['message' => 'Business not found.'], 404);
        }
    }

    public function updateUserMeta(Request $request, $userId)
    {
        $validated = $request->validate([
            'country' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:255',
        ]);
        $userMeta = UserMeta::where('user_id', $userId)->first();
        if (!$userMeta) {
            return response()->json(['message' => 'User meta not found'], 404);
        }
        // Update only the fields that are present in the request
        if (isset($validated['country'])) {
            $userMeta->country = $validated['country'];
        }
        if (isset($validated['language'])) {
            $userMeta->language = $validated['language'];
        }
        if (isset($validated['currency'])) {
            $userMeta->currency = $validated['currency'];
        }
        $userMeta->save();
        return response()->json([
            'message' => 'User meta updated successfully',
            'data' => $userMeta
        ], 200);
    }

    public function get_consumer_profile($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $baseUrl2 = request()->getSchemeAndHttpHost();
        $profileImageUrl = $baseUrl2 . '/' . $user->profile_image;

        $data = [
            'profile_image' => $profileImageUrl,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_id' => $user->id,
            'email' => $user->email,
            'password' => $user->password,

        ];
        return response()->json([
            'message' => 'Profile data retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public function update_consumer_profile(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {

            if ($request->has('email') && $request->email !== '') {
                $existingUser = User::where('email', $request->email)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'message' => 'Email is already in use by another user'
                    ], 400);
                }

                $user->email = $request->email;
            }

            if ($request->has('first_name') && $request->first_name !== '') {
                $user->first_name = $request->first_name;
            }

            if ($request->has('last_name') && $request->last_name !== '') {
                $user->last_name = $request->last_name;
            }

            if ($request->has('profile_image') && $request->profile_image !== '') {
                $oldImage = $user->profile_image;
                $imageData = $request->profile_image;
                $imageName = 'profile_' . time() . '.png';
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $imageData = base64_decode($imageData);
                $imagePath = public_path('user/' . $imageName);
                file_put_contents($imagePath, $imageData);
                $user->profile_image = 'user/' . $imageName;

                if ($oldImage && $oldImage !== $user->profile_image) {
                    $oldImagePath = public_path($oldImage);
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }
            }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
        }

        return response()->json([
            'message' => 'User not found'
        ], 404);
    }
}
