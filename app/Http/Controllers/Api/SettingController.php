<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function get_splash_screen_data()
    {
        $baseUrl = rtrim(config('app.url'), '/') . '/public/media/setting/';

        $keys = [
            'logo',
            'sec_spl_srn_image',
            'sec_spl_srn_title',
            'sec_spl_srn_desc',
            'business_spl_srn_image',
            'business_spl_srn_title',
            'consumer_spl_srn_image',
            'consumer_spl_srn_title',
        ];

        $settings = DB::table('settings')
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        foreach (['logo', 'sec_spl_srn_image', 'business_spl_srn_image', 'consumer_spl_srn_image'] as $imageKey) {
            if (!empty($settings[$imageKey])) {
                $settings[$imageKey] = $baseUrl . ltrim($settings[$imageKey], '/');
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
                    'title' => $settings['business_spl_srn_title'] ?? null,
                ],
                'consumer' => [
                    'image' => $settings['consumer_spl_srn_image'] ?? null,
                    'title' => $settings['consumer_spl_srn_title'] ?? null,
                ],
            ],
        ]);
    }
}
