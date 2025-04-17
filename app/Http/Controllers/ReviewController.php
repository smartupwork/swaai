<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function storeReview(Request $request)
    {

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'review_text' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);


        $user_id = auth()->id();

        // Insert review into the database
        $reviewId = DB::table('reviews')->insertGetId([
            'business_id' => $validated['business_id'],
            'user_id' => $user_id,
            'review_text' => $validated['review_text'],
            'rating' => $validated['rating'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Review submitted successfully!',
            'review_id' => $reviewId
        ], 201);
    }

    public function getReviews($business_id)
    {
        $baseUrl = url('/');


        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.business_id', $business_id)
            ->select('reviews.id', 'reviews.review_text', 'reviews.rating', 'reviews.created_at', 'users.first_name', 'users.last_name', 'users.profile_image')
            ->orderBy('reviews.created_at', 'desc')
            ->get()
            ->map(function ($review) use ($baseUrl) {
                $review->profile_image = $review->profile_image ? $baseUrl . '/' . ltrim($review->profile_image, '/') : null;
                return $review;
            });

        return response()->json([
            'business_id' => $business_id,
            'reviews' => $reviews
        ]);
    }
}
