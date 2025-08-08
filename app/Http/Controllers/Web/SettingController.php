<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class SettingController extends Controller
{
    public function index()
    {

        $settings = Setting::first();

        return view('admin.settings.index', ['settings' => $settings]);
    }

    public function logo(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,svg|max:20481'
        ]);


        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create();
        }

        if ($request->hasFile('logo')) {

            if (!empty($setting->logo)) {
                $oldPath = public_path('media/setting/' . $setting->logo);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('media/setting'), $filename);

            $setting->update([
                'logo' => $filename,
            ]);
        }

        return redirect()->back()->with('success', 'Logo updated successfully.');
    }

    public function updateSecondSplash(Request $request)
    {
        $request->validate([
            'sec_spl_srn_image' => 'nullable|file|image|mimes:jpeg,png,jpg,svg|max:20481',
            'sec_spl_srn_title' => 'nullable|string|max:855',
            'sec_spl_srn_desc'  => 'nullable|string|max:1000',
        ]);


        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create();
        }


        if ($request->hasFile('sec_spl_srn_image')) {

            if (!empty($setting->sec_spl_srn_image)) {
                $oldPath = public_path('media/setting/' . $setting->sec_spl_srn_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('sec_spl_srn_image');
            $filename = 'sec_spl_srn_image_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('media/setting'), $filename);
            $setting->sec_spl_srn_image = $filename;
        }


        $setting->sec_spl_srn_title = $request->input('sec_spl_srn_title');
        $setting->sec_spl_srn_desc = $request->input('sec_spl_srn_desc');


        $setting->save();

        return redirect()->back()->with('success', 'Second splash screen settings updated successfully.');
    }

    public function updateBusinessConsumerSplash(Request $request)
    {
        $request->validate([
            'business_spl_srn_image'   => 'nullable|file|image|mimes:jpeg,png,jpg,svg|max:20481',
            'business_spl_srn_title'   => 'nullable|string|max:855',
            'business_sec_spl_srn_image'   => 'nullable|file|image|mimes:jpeg,png,jpg,svg|max:20481',
            'consumer_spl_srn_image'   => 'nullable|file|image|mimes:jpeg,png,jpg,svg|max:20481',
            'consumer_spl_srn_title'   => 'nullable|string|max:855',
        ]);

        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create(); 
        }

       
        if ($request->hasFile('business_spl_srn_image')) {
            if (!empty($setting->business_spl_srn_image)) {
                $oldPath = public_path('media/setting/' . $setting->business_spl_srn_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('business_spl_srn_image');
            $filename = 'business_spl_srn_image_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('media/setting'), $filename);
            $setting->business_spl_srn_image = $filename;
        }

        if ($request->hasFile('business_sec_spl_srn_image')) {
            if (!empty($setting->business_sec_spl_srn_image)) {
                $oldPath = public_path('media/setting/' . $setting->business_sec_spl_srn_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('business_sec_spl_srn_image');
            $filename = 'business_sec_spl_srn_image' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('media/setting'), $filename);
            $setting->business_sec_spl_srn_image = $filename;
        }

        
        if ($request->hasFile('consumer_spl_srn_image')) {
            if (!empty($setting->consumer_spl_srn_image)) {
                $oldPath = public_path('media/setting/' . $setting->consumer_spl_srn_image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('consumer_spl_srn_image');
            $filename = 'consumer_spl_srn_image_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('media/setting'), $filename);
            $setting->consumer_spl_srn_image = $filename;
        }

        
        $setting->business_spl_srn_title = $request->input('business_spl_srn_title');
        $setting->consumer_spl_srn_title = $request->input('consumer_spl_srn_title');

        $setting->save();

        return redirect()->back()->with('success', 'Business & Consumer splash screen settings updated successfully.');
    }
}
