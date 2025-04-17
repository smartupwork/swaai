<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Business;
use App\Models\UserMedia;
use App\Models\User;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use App\Models\SavedBusiness;
use Illuminate\Support\Facades\Auth;
use App\Models\CheckInBusiness;
use App\Services\GoogleMapsService;
use App\Models\BusinessVisit;
use App\Models\BusinessAnalytic;
use Carbon\Carbon;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use App\Models\BusinessAnalyticsHistory;

class BusinessController extends Controller
{


    public function getBusiness()
    {
        $baseUrl = config('app.url') . '/';

        $businesses = DB::table('businesses as b')
            ->join('categories as c', 'b.cat_id', '=', 'c.id')
            ->select(
                'b.id',
                'b.name',
                'c.name as category_name',
                DB::raw("(SELECT images 
                      FROM users_media 
                      WHERE users_media.user_id = b.user_id 
                        AND users_media.images IS NOT NULL 
                      ORDER BY id ASC 
                      LIMIT 1) as image")
            )
            ->get();

        $formattedBusinesses = $businesses->map(function ($business) use ($baseUrl) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category_name,
                'description' => $business->description ?? null,
                'image' => $business->image ? $baseUrl . $business->image : null,
            ];
        });

        return response()->json($formattedBusinesses);
    }


    public function getBusinessDetail($business_id)
    {
        $baseUrl = url('/');


        $business = DB::table('businesses')
            ->where('id', $business_id)
            ->first();

        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }


        $user = DB::table('users')
            ->where('id', $business->user_id)
            ->select('profile_image')
            ->first();


        $profileImage = $user && $user->profile_image ? $baseUrl . '/' . ltrim($user->profile_image, '/') : null;


        $reviews = DB::table('reviews')->where('business_id', $business_id)->get();


        $media = DB::table('users_media')
            ->where('user_id', $business->user_id)
            ->select('id', 'images', 'videos', 'title', 'description', 'image_redirect_url')
            ->get()
            ->map(function ($item) use ($baseUrl) {
                $item->images = $item->images ? $baseUrl . '/' . ltrim($item->images, '/') : null;
                $item->videos = $item->videos ? $baseUrl . '/' . ltrim($item->videos, '/') : null;
                return $item;
            });

        return response()->json([
            'business' => $business,
            'profile_image' => $profileImage,
            'reviews' => $reviews,
            'media' => $media
        ]);
    }

    public function getCategories()
    {
        $categories = Category::all();
        if ($categories) {
            return response()->json([
                'data' => $categories
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not Found!'
            ], 404);
        }
    }



    public function getReccomend(Request $request)
    {
        $userId = auth()->id();
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $defaultDistance = 15; // Default search radius in miles
        $baseUrl = config('app.url') . '/'; // Adjust if needed

        // Get category IDs from saved & checked-in businesses
        $userCategories = DB::table('businesses')
            ->whereIn('id', function ($query) use ($userId) {
                $query->select('business_id')
                    ->from('saved_businesses')
                    ->where('user_id', $userId)
                    ->union(
                        DB::table('checked_in_businesses')
                            ->select('business_id')
                            ->where('user_id', $userId)
                    );
            })
            ->pluck('cat_id')
            ->unique();

        // Fetch recommended businesses with media
        $recommendedBusinesses = Business::whereIn('cat_id', $userCategories)
            ->where('status', 1)
            ->leftJoin('users_media', 'businesses.user_id', '=', 'users_media.user_id')
            ->select(
                'businesses.*',
                DB::raw("CONCAT('$baseUrl', users_media.images) as media_image"),
                'users_media.title as media_title',
                'users_media.description as media_description',
                'users_media.image_redirect_url'
            )
            ->get();

        // Fetch nearby businesses (within 15 miles) with media
        $nearbyBusinesses = Business::select(
            'businesses.*',
            DB::raw("(3959 * acos(
                cos(radians(?)) * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) * sin(radians(latitude))
            )) AS distance"),
            DB::raw("CONCAT('$baseUrl', users_media.images) as media_image"),
            'users_media.title as media_title',
            'users_media.description as media_description',
            'users_media.image_redirect_url'
        )
            ->addBinding([$latitude, $longitude, $latitude], 'select')
            ->leftJoin('users_media', 'businesses.user_id', '=', 'users_media.user_id')
            ->where('businesses.status', 1)
            ->whereNotNull('businesses.latitude')
            ->whereNotNull('businesses.longitude')
            ->where('businesses.latitude', '!=', 0)
            ->where('businesses.longitude', '!=', 0)
            ->having('distance', '<=', $defaultDistance)
            ->orderBy('distance', 'asc')
            ->get();

        // Check for empty results
        if ($recommendedBusinesses->isEmpty() && $nearbyBusinesses->isEmpty()) {
            return response()->json([
                'recommended_businesses' => [],
                'nearby_businesses' => [],
                'message' => 'No recommended or nearby businesses found.',
            ], 404);
        }

        if ($recommendedBusinesses->isEmpty()) {
            return response()->json([
                'recommended_businesses' => [],
                'nearby_businesses' => $nearbyBusinesses,
                'message' => 'No recommended businesses found, but here are some nearby businesses.',
            ]);
        }

        if ($nearbyBusinesses->isEmpty()) {
            return response()->json([
                'recommended_businesses' => $recommendedBusinesses,
                'nearby_businesses' => [],
                'message' => 'No nearby businesses found, but here are some recommended businesses.',
            ]);
        }

        // Return response with both lists
        return response()->json([
            'recommended_businesses' => $recommendedBusinesses,
            'nearby_businesses' => $nearbyBusinesses,
            'message' => 'Recommended and nearby businesses retrieved successfully.',
        ]);
    }



    public function createBusiness(Request $request, GoogleMapsService $googleMapsService)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cat_id' => 'required|integer',
            'business_name' => 'required|string',
            'description' => 'nullable|string',
            'website_url' => 'nullable|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|string',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed.'
            ], 422);
        }

        $fields = $validator->validated();
        $fullAddress = "{$fields['street']}, {$fields['city']}, {$fields['state']}, {$fields['zipcode']}, {$fields['country']}";

        // echo $fullAddress;
        // exit;
        $coordinates = $googleMapsService->getCoordinatesFromAddress($fullAddress);

        if (!$coordinates) {
            return response()->json(['message' => 'Invalid address. Please enter a valid location.'], 400);
        }

        $business = new Business();
        $business->user_id = $fields['user_id'];
        $business->cat_id = $fields['cat_id'];
        $business->name = $fields['business_name'];
        $business->description = $fields['description'];
        $business->website_url = $fields['website_url'] ?? 'example.abc.test';
        $business->business_address = $fullAddress;
        $business->is_featured = 1;
        $business->latitude = $coordinates['latitude'];
        $business->longitude = $coordinates['longitude'];

        $business->save();

        return response()->json([
            'data' => $business,
            'message' => 'Business created successfully.'
        ], 201);
    }


    public function searchBusinesses(Request $request)
    {
        $baseUrl = config('app.url') . '/';

        $latitude = $request->input('latitude', null);
        $longitude = $request->input('longitude', null);
        $categoryId = $request->input('category', null);
        $minRating = $request->input('min_reviews', null);
        $maxDistance = $request->input('max_distance', null);

        $query = Business::select(
            'businesses.*',
            DB::raw("(SELECT images 
                  FROM users_media 
                  WHERE users_media.user_id = businesses.user_id 
                    AND users_media.images IS NOT NULL 
                  ORDER BY id ASC 
                  LIMIT 1) as image")
        );

        if (!empty($latitude) && !empty($longitude) && is_numeric($latitude) && is_numeric($longitude)) {
            $query->addSelect(DB::raw("(3959 * acos(
            cos(radians(?)) * cos(radians(latitude)) 
            * cos(radians(longitude) - radians(?)) 
            + sin(radians(?)) * sin(radians(latitude))
        )) AS distance"))
                ->addBinding([$latitude, $longitude, $latitude], 'select')
                ->whereNotNull('businesses.latitude')
                ->whereNotNull('businesses.longitude')
                ->where('businesses.latitude', '!=', 0)
                ->where('businesses.longitude', '!=', 0);
        }

        $query->leftJoin('reviews', 'reviews.business_id', '=', 'businesses.id')
            ->groupBy([
                'businesses.id',
                'businesses.name',
                'businesses.latitude',
                'businesses.longitude',
                'businesses.cat_id',
                'businesses.user_id',
                'businesses.description',
                'businesses.business_address',
                'businesses.website_url',
                'businesses.is_featured',
                'businesses.status',
                'businesses.created_at',
                'businesses.updated_at'
            ])
            ->addSelect(DB::raw("COALESCE(AVG(reviews.rating), 0) as average_rating"));

        if (!empty($categoryId)) {
            $query->where('businesses.cat_id', $categoryId);
        }

        if (!empty($minRating)) {
            $query->having('average_rating', '>=', (float) $minRating);
        }

        if (!empty($latitude) && !empty($longitude) && !empty($maxDistance) && is_numeric($maxDistance)) {
            $query->having('distance', '<=', (float) $maxDistance);
        }

        if (!empty($latitude) && !empty($longitude)) {
            $query->orderBy('distance', 'asc');
        }

        $businesses = $query->get();

        if ($businesses->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'No businesses found matching your criteria.',
            ], 404);
        }

        // Format businesses and append base URL to image
        $formattedBusinesses = $businesses->map(function ($business) use ($baseUrl) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category_id' => $business->cat_id,
                'description' => $business->description,
                'business_address' => $business->business_address,
                'website_url' => $business->website_url,
                'average_rating' => $business->average_rating,
                'distance' => $business->distance ?? null,
                'image' => $business->image ? $baseUrl . $business->image : null,
            ];
        });

        return response()->json([
            'data' => $formattedBusinesses,
            'message' => 'Businesses retrieved successfully.',
        ]);
    }



    public function getMediaByUser($id)
    {
        if (!User::where('id', $id)->exists()) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }
        $media = UserMedia::where('user_id', $id)->get(['id', 'images', 'videos', 'title', 'description', 'image_redirect_url']);
        if ($media->isEmpty()) {
            return response()->json([
                'message' => 'No media found for the specified user.',
                'data' => [],
            ], 200);
        }
        $response = $media->map(function ($item) {
            return [
                'media_id' => $item->id,
                'image_url' => $item->images ? url($item->images) : null,
                'video_url' => $item->videos ? url($item->videos) : null,
                'title' => $item->title,
                'description' => $item->description,
                'image_redirect_url' => $item->image_redirect_url,
            ];
        });
        return response()->json([
            'message' => 'Media fetched successfully.',
            'data' => $response,
        ], 200);
    }

    public function insertVideo(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'video' => 'nullable|file|max:88120', // Max size is 88 MB
            'media_id' => 'nullable|exists:users_media,id', // Ensure media_id is valid if provided
        ]);
        $videoFile = null;
        $videoPath = null;
        $oldVideoPath = null;
        if ($request->hasFile('video')) {
            $videoFile = $request->file('video');
            $videoPath = 'media/videos/' . time() . '_' . $videoFile->getClientOriginalName();
        }
        if (!empty($request->input('media_id'))) {
            $media = UserMedia::find($request->input('media_id'));
            if ($media) {
                $oldVideoPath = $media->videos;
                $media->videos = $videoPath;
                $media->save();
                if ($videoFile) {
                    $videoFile->move(public_path('media/videos'), basename($videoPath));
                }
                if ($oldVideoPath && file_exists(public_path($oldVideoPath))) {
                    unlink(public_path($oldVideoPath));
                }
                return response()->json([
                    'message' => 'Video updated successfully.',
                    'data' => $media,
                ], 200);
            }
        } else {
            if ($videoFile) {
                $media = new UserMedia();
                $media->user_id = $validated['user_id'];
                $media->videos = $videoPath;
                $media->save();
                $videoFile->move(public_path('media/videos'), basename($videoPath));
                return response()->json([
                    'message' => 'Video created successfully.',
                    'data' => $media,
                ], 201);
            }
        }
        return response()->json([
            'message' => 'No video provided or media_id not found. No action taken.',
        ], 400);
    }


    public function insertImage(Request $request)
    {

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'image' => 'required|string', // Base64 image input
            'media_id' => 'nullable|exists:users_media,id',
            'title' => 'nullable',
            'description' => 'nullable',
            'image_redirect_url' => 'nullable',
        ]);

        $imagePath = null;
        $oldImagePath = null;
        $decodedImage = null;

        // Decode the Base64 image
        $imageInput = $request->input('image');
        if (preg_match('/^data:image\/(\w+);base64,/', $imageInput, $matches)) {
            $extension = $matches[1]; // Extract file extension (e.g., jpeg, png)
            $imageBase64 = substr($imageInput, strpos($imageInput, ',') + 1);
            $decodedImage = base64_decode($imageBase64);

            if ($decodedImage === false) {
                return response()->json(['message' => 'Invalid base64 image data.'], 400);
            }

            // Generate a unique file name
            $imageName = time() . '_image.' . $extension;
            $imagePath = 'media/images/' . $imageName;
        } else {
            return response()->json(['message' => 'Invalid image format. Base64 string required.'], 400);
        }

        if (!empty($request->input('media_id'))) {
            // Update existing media entry
            $media = UserMedia::find($request->input('media_id'));
            if ($media) {
                $oldImagePath = $media->images;
                $media->images = $imagePath;
                $media->title = $validated['title'] ?? $media->title;
                $media->description = $validated['description'] ?? $media->description;
                $media->image_redirect_url = $validated['image_redirect_url'] ?? $media->image_redirect_url;
                $media->save();

                // Save the image to the directory after the database update
                file_put_contents(public_path($imagePath), $decodedImage);

                // Delete the old image file if it exists
                if ($oldImagePath && file_exists(public_path($oldImagePath))) {
                    unlink(public_path($oldImagePath));
                }

                return response()->json([
                    'message' => 'Image updated successfully.',
                    'data' => $media,
                ], 200);
            }
        } else {
            // Create a new media entry
            $media = new UserMedia();
            $media->user_id = $validated['user_id'];
            $media->images = $imagePath;
            $media->title = $validated['title'] ?? null;
            $media->description = $validated['description'] ?? null;
            $media->image_redirect_url = $validated['image_redirect_url'] ?? null;
            $media->save();

            // Save the image to the directory after the database insert
            file_put_contents(public_path($imagePath), $decodedImage);

            return response()->json([
                'message' => 'Image created successfully.',
                'data' => $media,
            ], 201);
        }

        return response()->json([
            'message' => 'Media ID not found. No action taken.',
        ], 400);
    }


    public function getOffers($id)
    {
        $offers = Offer::where('user_id',  $id)->get();
        if ($offers) {
            return response()->json([
                'message' => 'list of offers',
                'data' => $offers,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Something went wrong',
            ], 401);
        }
    }

    public function createOffers(Request $request)
    {
        $validated = $request->validate([
            'offer_id' => 'nullable|exists:offers,id',
            'user_id' => 'required|exists:users,id',
            'business_id' => 'required',
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);
        if (!empty($validated['offer_id'])) {
            $offer = Offer::find($validated['offer_id']);
            if ($offer) {
                $offer->update([
                    'user_id' => $validated['user_id'],
                    'business_id' => $validated['business_id'],
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? $offer->description,
                ]);
            }
            $message = 'Offer updated successfully.';
        } else {
            $offer = Offer::create([
                'user_id' => $validated['user_id'],
                'business_id' => $validated['business_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);
            $message = 'Offer created successfully.';
        }
        return response()->json([
            'message' => $message,
            'data' => $offer,
        ], 200);
    }

    public function getBusinessOffer($business_id)
    {
        $user = Auth::user();
        $alreadyVisited = BusinessVisit::where('user_id', $user->id)
            ->where('business_id', $business_id)
            ->exists();

        if ($alreadyVisited) {
            return response()->json([
                'success' => false,
                'message' => 'You have already visited this business. No new offers available.'
            ]);
        }

        $offer = Offer::where('business_id', $business_id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($offer) {

            BusinessVisit::create([
                'user_id' => $user->id,
                'business_id' => $business_id
            ]);

            return response()->json([
                'success' => true,
                'offer' => [
                    'name' => $offer->name,
                    'description' => $offer->description,
                    'price' => $offer->price,
                ],
                'message' => 'Special offer available!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No special offers available for this business.'
        ]);
    }

    public function toggleSaveBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        $userId = Auth::id();
        $businessId = $request->business_id;

        $saved = SavedBusiness::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->first();

        if ($saved) {
            $saved->delete();
            return response()->json(['message' => 'Business unsaved successfully.'], 200);
        } else {
            SavedBusiness::create([
                'user_id' => $userId,
                'business_id' => $businessId,
                'saved_at' => now(),
            ]);
            return response()->json(['message' => 'Business saved successfully.'], 201);
        }
    }

    public function getSavedBusinesses()
    {
        $userId = Auth::id();
        $baseUrl = config('app.url') . '/';

        $savedBusinesses = DB::table('saved_businesses as sb')
            ->join('businesses as b', 'sb.business_id', '=', 'b.id')
            ->join('categories as c', 'b.cat_id', '=', 'c.id')
            ->select(
                'b.id',
                'b.name',
                'c.name as category_name',
                DB::raw("(SELECT images 
                      FROM users_media 
                      WHERE users_media.user_id = b.user_id 
                        AND users_media.images IS NOT NULL 
                      ORDER BY id ASC 
                      LIMIT 1) as image")
            )
            ->where('sb.user_id', $userId)
            ->get();

        $formattedBusinesses = $savedBusinesses->map(function ($business) use ($baseUrl) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category_name,
                'description' => $business->description ?? null,
                'image' => $business->image ? $baseUrl . $business->image : null,
            ];
        });

        return response()->json($formattedBusinesses);
    }

    public function toggleCheckInBusiness(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);

        $userId = Auth::id();
        $businessId = $request->business_id;

        $existingCheckIn = CheckInBusiness::where('user_id', $userId)
            ->where('business_id', $businessId)
            ->first();

        if ($existingCheckIn) {

            $existingCheckIn->delete();

            return response()->json([
                'message' => 'Business unchecked successfully.',
                'checked_in' => false
            ], 200);
        } else {
            CheckInBusiness::create([
                'user_id' => $userId,
                'business_id' => $businessId,
            ]);

            return response()->json([
                'message' => 'Business checked in successfully.',
                'checked_in' => true
            ], 200);
        }
    }

    public function getCheckedInBusinesses()
    {
        $userId = auth()->id();
        $baseUrl = config('app.url') . '/';

        $checkedInBusinesses = DB::table('checked_in_businesses as c')
            ->join('businesses as b', 'c.business_id', '=', 'b.id')
            ->join('categories as cat', 'b.cat_id', '=', 'cat.id')
            ->select(
                'b.id',
                'b.name',
                'b.description',
                'cat.name as category_name',
                DB::raw("(SELECT images 
                      FROM users_media 
                      WHERE users_media.user_id = b.user_id 
                        AND users_media.images IS NOT NULL 
                      ORDER BY id ASC 
                      LIMIT 1) as image")
            )
            ->where('c.user_id', $userId)
            ->get();

        $formattedBusinesses = $checkedInBusinesses->map(function ($business) use ($baseUrl) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'category' => $business->category_name,
                'description' => $business->description ?? null,
                'image' => $business->image ? $baseUrl . $business->image : null,
            ];
        });

        return response()->json($formattedBusinesses);
    }

    public function saveAnalytics(Request $request)
    {
        $userId = Auth::id();

        $validatedData = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'event_type'  => 'required|in:visit,video_play,coupon,click',
        ]);

        $now = now();

        
        $analytics = BusinessAnalytic::where('user_id', $userId)
            ->where('business_id', $validatedData['business_id'])
            ->first();

        if (!$analytics) {
            $analytics = BusinessAnalytic::create([
                'user_id'             => $userId,
                'business_id'         => $validatedData['business_id'],
                'page_visits'         => 0,
                'unique_visits'       => 0,
                'video_views'         => 0,
                'unique_video_visits' => 0,
                'coupon_selection'    => 0,
                'website_clicks'      => 0,
                'last_visit_time'     => null,
                'last_video_view_time' => null
            ]);
        }

        
        $lastVisitTime = $analytics->last_visit_time ? Carbon::parse($analytics->last_visit_time) : null;
        $lastVideoTime = $analytics->last_video_view_time ? Carbon::parse($analytics->last_video_view_time) : null;

        switch ($validatedData['event_type']) {
            case 'visit':
                
                if ($lastVisitTime === null || $lastVisitTime->diffInHours($now) >= 24) {
                    $analytics->increment('unique_visits');
                    $analytics->last_visit_time = $now;
                }
                $analytics->increment('page_visits');
                break;

            case 'video_play':
               
                if ($lastVideoTime === null || $lastVideoTime->diffInHours($now) >= 24) {
                    $analytics->increment('unique_video_visits');
                    $analytics->last_video_view_time = $now;
                }
                $analytics->increment('video_views');
                break;

            case 'coupon':
                $analytics->increment('coupon_selection');
                break;

            case 'click':
                $analytics->increment('website_clicks');
                break;
        }

        $analytics->save();

        $this->saveToHistory($analytics);

        return response()->json([
            'message' => 'Analytics updated successfully!',
            'data'    => $analytics
        ], 200);
    }

    private function saveToHistory($analytics)
    {
        
        BusinessAnalyticsHistory::create([
            'user_id'             => $analytics->user_id,
            'business_id'         => $analytics->business_id,
            'page_visits'         => $analytics->page_visits,
            'unique_visits'       => $analytics->unique_visits,
            'video_views'         => $analytics->video_views,
            'unique_video_visits' => $analytics->unique_video_visits,
            'coupon_selection'    => $analytics->coupon_selection,
            'website_clicks'      => $analytics->website_clicks,
            'recorded_at'         => now()
        ]);
    }

    public function getAnalytics($business_id)
    {
        $business = Business::find($business_id);
        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }


        $analyticsExists = BusinessAnalytic::where('business_id', $business_id)->exists();


        if (!$analyticsExists) {
            return response()->json([
                'business_id' => $business_id,
                'total_page_visits' => 0,
                'total_unique_visits' => 0,
                'total_video_views' => 0,
                'total_unique_video_views' => 0,
                'total_website_clicks' => 0,
                'total_coupon_selections' => 0,
                'average_rating' => round(Review::where('business_id', $business_id)->avg('rating'), 2) ?? 0
            ]);
        }


        $analytics = BusinessAnalytic::where('business_id', $business_id)
            ->selectRaw('
            SUM(page_visits) as total_page_visits,
            SUM(unique_visits) as total_unique_visits,
            SUM(video_views) as total_video_views,
            SUM(unique_video_visits) as total_unique_video_views,
            SUM(website_clicks) as total_website_clicks,
            SUM(coupon_selection) as total_coupon_selections
        ')
            ->first();


        $average_rating = Review::where('business_id', $business_id)->avg('rating');

        return response()->json([
            'business_id' => $business_id,
            'total_page_visits' => $analytics->total_page_visits ?? 0,
            'total_unique_visits' => $analytics->total_unique_visits ?? 0,
            'total_video_views' => $analytics->total_video_views ?? 0,
            'total_unique_video_views' => $analytics->total_unique_video_views ?? 0,
            'total_website_clicks' => $analytics->total_website_clicks ?? 0,
            'total_coupon_selections' => $analytics->total_coupon_selections ?? 0,
            'average_rating' => round($average_rating, 2) ?? 0
        ]);
    }

    public function filterAnalytics(Request $request, $business_id)
    {
        $validatedData = $request->validate([
            'filter' => 'nullable|in:30_days,90_days,120_days,6_months,1_year,overall',
        ]);

        $filter = $validatedData['filter'] ?? 'overall';
        $now = now();
        $startDate = $this->getStartDateByFilter($filter);

        $analytics = DB::table('business_analytics_history as b')
        ->leftJoin('reviews as r', 'b.business_id', '=', 'r.business_id')
        ->where('b.business_id', $business_id)
        ->where('b.recorded_at', '>=', $startDate)
        ->where('b.recorded_at', '<=', $now)
        ->select(
            DB::raw('SUM(b.page_visits) as total_page_visits'),
            DB::raw('SUM(b.unique_visits) as total_unique_visits'),
            DB::raw('SUM(b.video_views) as total_video_views'),
            DB::raw('SUM(b.unique_video_visits) as total_unique_video_visits'),
            DB::raw('SUM(b.coupon_selection) as total_coupon_selections'),
            DB::raw('SUM(b.website_clicks) as total_website_clicks'),
            DB::raw('AVG(r.rating) as average_rating')
        )
        ->first();

    if (!$analytics) {
        return response()->json(['message' => 'No analytics data found for the selected filter.'], 404);
    }

    return response()->json([
        'message' => 'Analytics data retrieved successfully!',
        'data' => $analytics
    ], 200);
    }

    private function getStartDateByFilter($filter)
    {
        switch ($filter) {
            case '30_days':
                return Carbon::now()->subDays(30)->toDateString();
            case '90_days':
                return Carbon::now()->subDays(90)->toDateString();
            case '120_days':
                return Carbon::now()->subDays(120)->toDateString();
            case '6_months':
                return Carbon::now()->subMonths(6)->toDateString();
            case '1_year':
                return Carbon::now()->subYear()->toDateString();
            case 'overall':
                return '1970-01-01';
            default:
                return Carbon::now()->subDays(30)->toDateString();
        }
    }
}
