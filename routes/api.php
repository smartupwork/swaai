<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostShareController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConsumerController;
use App\Models\Business;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::get('/plans', [StripeController::class, 'getPlans']);
    Route::post('/create-subscription', [StripeController::class, 'subscribeWithCard']);
    Route::get('/categories', [BusinessController::class, 'getCategories']);
    Route::get('/sub/categories', [BusinessController::class, 'get_sub_categories']);
    //Route::post('/checkout', [StripeController::class, 'checkout']);
    
    // Business Routes
    Route::post('/createbusiness', [BusinessController::class, 'createBusiness']);
    Route::get('/get-business', [BusinessController::class, 'getBusiness']);
    Route::get('/get-business/detail/{business_id}', [BusinessController::class, 'getBusinessDetail']);
    Route::post('/search-business', [BusinessController::class, 'searchBusinesses']);
    Route::post('/insert-video', [BusinessController::class, 'insertVideo']);
    Route::post('/insert-image', [BusinessController::class, 'insertImage']);
    Route::get('/get-user-media/{id}', [BusinessController::class, 'getMediaByUser']);

    Route::post('/get-recommended/businesses', [BusinessController::class, 'getReccomend']);
    Route::post('/businesses/save-analytics', [BusinessController::class, 'saveAnalytics']);
    Route::get('/businesses/get-analytics/{business_id}', [BusinessController::class, 'getAnalytics']);
    Route::get('/business/filter-analytics/{business_id}', [BusinessController::class, 'filterAnalytics']);

    Route::get('/offers/{id}', [BusinessController::class, 'getOffers']);
    Route::get('/avail/offer/{business_id}/', [BusinessController::class, 'getBusinessOffer']);
    Route::post('/saveOffers', [BusinessController::class, 'createOffers']);
    Route::get('/profile/{id}', [ProfileController::class, 'getProfile']);
    Route::post('/updateProfile', [ProfileController::class, 'updateProfile']);
    Route::put('/updateAddress/{id}', [ProfileController::class, 'updateBusinessAddress']);
    Route::patch('user-meta/{userId}', [ProfileController::class, 'updateUserMeta']);

    Route::post('/add-card', [StripeController::class, 'addNewCard']);
    Route::get('/get-cards/{id}', [StripeController::class, 'getAllCards']);
    Route::post('/make-card-default', [StripeController::class, 'setDefaultCard']);

    Route::get('/get-communities/{id}', [CommunityController::class, 'getCommunities']);
    Route::post('/add-community', [CommunityController::class, 'addNewCommunity']);

    Route::post('/community/{id}/join', [CommunityController::class, 'joinCommunity']);
    Route::post('/community/{id}/leave', [CommunityController::class, 'leaveCommunity']);
    Route::get('/community/members/{id}', [CommunityController::class, 'membersCount']);

    Route::get('/get-posts/{id}', [PostController::class, 'getPosts']);
    Route::get('/get-my-posts/{id}', [PostController::class, 'getMyPosts']);
    Route::post('/add-posts', [PostController::class, 'addPosts']);

    Route::post('react-to-post', [ReactionController::class, 'storeReaction']);
    Route::post('comment-in-post', [CommentController::class, 'storeComment']);
    Route::post('post-shares', [PostShareController::class, 'storeShare']);

    Route::post('add-review', [ReviewController::class, 'storeReview']);
    Route::get('/get-review/{business_id}', [ReviewController::class, 'getReviews']);

    Route::post('/businesses/toggle-save', [BusinessController::class, 'toggleSaveBusiness']);
    Route::get('/user/saved-businesses', [BusinessController::class, 'getSavedBusinesses']);

    Route::post('/businesses/toggle-check-in', [BusinessController::class, 'toggleCheckInBusiness']);
    Route::get('/businesses/checked-in', [BusinessController::class, 'getCheckedInBusinesses']);

    Route::get('/consumer/profile/{id}', [ProfileController::class, 'get_consumer_profile']);
    Route::post('/consumer/updateProfile', [ProfileController::class, 'update_consumer_profile']);

    Route::get('/chats', [ChatController::class, 'getConversations']);
    Route::get('/chat/{user_id}', [ChatController::class, 'getChatMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::post('/chat/read/{user_id}', [ChatController::class, 'markAsRead']);

    Route::get('/consumer/calculate-impact', [ConsumerController::class, 'calculateImpact']);
    

});

Route::post('password/forgot', [AuthenticationController::class, 'requestReset']);
Route::post('password/reset', [AuthenticationController::class, 'resetPassword']);
