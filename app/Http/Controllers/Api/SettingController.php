<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function get_splash_screen_data()
    {
        $baseUrl = rtrim(config('app.url'), '/') . '/public/media/setting/';

        $settings = DB::table('settings')->first();
        $settings = (array) $settings;

        foreach (['logo', 'sec_spl_srn_image', 'business_spl_srn_image', 'business_sec_spl_srn_image', 'consumer_spl_srn_image', 'botd_image'] as $imageKey) {
            if (!empty($settings[$imageKey])) {
                $settings[$imageKey] = $baseUrl . ltrim($settings[$imageKey], '/');
            }
        }

        
        $botd = null;
        if (!empty($settings['botd_business'])) {
            $business = DB::table('businesses')->where('id', $settings['botd_business'])->first();

            if ($business) {
                $botd = [
                    'image' => $settings['botd_image'] ?? null,
                    'business_id' => $business->id,
                    'business_type' => $business->business_type ?? null,
                    'heading' => $settings['botd_heading'] ?? null,
                    'business_name' => $business->name ?? null,
                ];
            }
        }

        return response()->json([
            'logo' => $settings['logo'] ?? null,
            'splash_screens' => [
                'second' => [
                    'image' => $settings['sec_spl_srn_image'] ?? null,
                    'title' => $settings['sec_spl_srn_title'] ?? null,
                    'description' => $settings['sec_spl_srn_desc'] ?? null,
                ],
                'business' => [
                    'image' => $settings['business_spl_srn_image'] ?? null,
                    'sec_image' => $settings['business_sec_spl_srn_image'] ?? null,
                    'title' => $settings['business_spl_srn_title'] ?? null,
                ],
                'consumer' => [
                    'image' => $settings['consumer_spl_srn_image'] ?? null,
                    'title' => $settings['consumer_spl_srn_title'] ?? null,
                ],
                'botd' => $botd,
            ],
        ]);
    }

    

    public function getBusinessTypes()
    {
        $businesstypes = BusinessType::all();

        if ($businesstypes) {
            return response()->json([
                'data' => $businesstypes
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not Found!'
            ], 404);
        }
    }
}
