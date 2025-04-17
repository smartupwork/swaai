<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAnalytic extends Model
{
    use HasFactory;

    protected $table = 'business_analytics';

    protected $fillable = [
        'user_id',
        'business_id',
        'page_visits',
        'coupon_selection',
        'video_views',
        'unique_visits',
        'unique_video_visits',
        'website_clicks',
        'last_video_view_time',
        'last_visit_time',
        'social_media_clicks',
        'rating',
        'created_at'
    ];
}
