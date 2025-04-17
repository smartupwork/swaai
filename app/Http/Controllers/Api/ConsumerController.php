<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConsumerImpact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsumerController extends Controller
{

    public function calculateImpact()
    {
        $userId = auth()->id();


        $allCategories = DB::table('categories')->select('id', 'name')->get();


        $businessImpacts = DB::table('businesses')
            ->leftJoin('saved_businesses', function ($join) use ($userId) {
                $join->on('businesses.id', '=', 'saved_businesses.business_id')
                    ->where('saved_businesses.user_id', $userId);
            })
            ->leftJoin('checked_in_businesses', function ($join) use ($userId) {
                $join->on('businesses.id', '=', 'checked_in_businesses.business_id')
                    ->where('checked_in_businesses.user_id', $userId);
            })
            ->leftJoin('business_visits', function ($join) use ($userId) {
                $join->on('businesses.id', '=', 'business_visits.business_id')
                    ->where('business_visits.user_id', $userId);
            })
            ->leftJoin('business_analytics', function ($join) use ($userId) {
                $join->on('businesses.id', '=', 'business_analytics.business_id')
                    ->where('business_analytics.user_id', $userId);
            })
            ->leftJoin('reviews', function ($join) use ($userId) {
                $join->on('businesses.id', '=', 'reviews.business_id')
                    ->where('reviews.user_id', $userId);
            })
            ->select(
                'businesses.cat_id as category_id',
                DB::raw('count(distinct businesses.id) as total_businesses'),
                DB::raw('count(distinct saved_businesses.id) as saved_count'),
                DB::raw('count(distinct checked_in_businesses.id) as check_in_count'),
                DB::raw('count(distinct business_visits.id) as visit_count'),
                DB::raw('count(distinct business_analytics.id) as website_clicks_count'),
                DB::raw('count(distinct reviews.id) as review_count')
            )
            ->groupBy('businesses.cat_id')
            ->get();


        $impactMap = [];
        foreach ($businessImpacts as $impact) {
            $totalActions = $impact->saved_count + $impact->check_in_count + $impact->visit_count + $impact->website_clicks_count + $impact->review_count;
            $impactMap[$impact->category_id] = [
                'total_actions' => $totalActions,
                'total_businesses' => $impact->total_businesses,
            ];
        }

        $final = [];
        foreach ($allCategories as $category) {
            $totalActions = $impactMap[$category->id]['total_actions'] ?? 0;
            $totalBusinesses = $impactMap[$category->id]['total_businesses'] ?? 0;
            $maxPossible = $totalBusinesses * 5;

            $percentage = $maxPossible > 0
                ? round(($totalActions / $maxPossible) * 100, 2)
                : 0;

            $final[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'impact_percentage' => min($percentage, 100),
            ];
        }

        return response()->json($final);
    }
}
