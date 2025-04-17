<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {

        $fields = $request->validate([
            'email' => 'required|email|unique:users',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|confirmed',

        ]);

        $fields['password'] = Hash::make($fields['password']);

        $user = User::create([
            'email' => $fields['email'],
            'password' => $fields['password'],
            'role_id' => $fields['role_id'],
        ]);

        $token = $user->createToken($request->email);

        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        // Check if the user has an associated business
        $businessExists = DB::table('businesses')->where('user_id', $user->id)->exists();

        $business = DB::table('businesses')->where('user_id', $user->id)->first();

        $business_id = $business ? $business->id : null;

        $token = $user->createToken($user->email);

        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken,
            'business_id' =>$business_id,
            'businesses_created' => $businessExists ? 1 : 0,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
    public function requestReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = User::where('email', $request->email)->first();
        $password = Str::random(rand(8, 10));
        $user->password = Hash::make($password);
        $user->save();
        $mail =  Mail::to($user->email)->send(new PasswordResetMail($user, $password));
        if ($mail) {
            return response()->json([
                'message' => 'A new password has been generated and sent to your email.',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Sorry, something went wrong.',
            ], 550);
        }
    }

    // public function resetPassword(Request $request)
    // {
    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email|exists:users,email',
    //         'token' => 'required',
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 422);
    //     }

    //     // Validate the Sanctum token
    //     $user = User::where('email', $request->email)->first();
    //     $tokenInstance = PersonalAccessToken::findToken($request->token);

    //     if (!$tokenInstance || $tokenInstance->tokenable_id !== $user->id || $tokenInstance->created_at->addMinutes(60)->isPast()) {
    //         return response()->json(['error' => 'Invalid or expired token.'], 400);
    //     }

    //     // Reset the user's password
    //     $user->password = Hash::make($request->password);
    //     $user->save();

    //     // Delete the token after resetting the password
    //     $tokenInstance->delete();

    //     return response()->json(['message' => 'Password reset successfully.']);
    // }
}
