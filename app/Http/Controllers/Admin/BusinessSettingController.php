<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Services\BusinessTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BusinessSettingController extends Controller
{
    public function index(BusinessTimeService $time)
    {
        $settings = BusinessSetting::query()->pluck('value', 'key');
        $hours = json_decode($settings['opening_hours'] ?? '{}', true) ?: [];
        $days = json_decode($settings['wholesale_delivery_days'] ?? '[]', true) ?: [];
        $status = $time->status();
        return view('admin.business-settings.index', compact('settings', 'hours', 'days', 'status'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_timezone' => 'required|string',
            'google_timezone_lat' => 'nullable|string',
            'google_timezone_lng' => 'nullable|string',
            'add_to_order_minutes' => 'required|integer|min:1|max:120',
            'drive_in_discount_percent' => 'required|numeric|min:0|max:100',
            'whatsapp_number' => 'required|string',
            'wholesale_delivery_start' => 'required',
            'wholesale_delivery_end' => 'required',
            'wholesale_cutoff_time' => 'required',
            'private_booking_title' => 'nullable|string',
            'private_booking_description' => 'nullable|string',
        ]);

        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $hours[$day] = [
                'open' => $request->input("open_$day", $day === 'sunday' ? '14:00' : '16:00'),
                'close' => $request->input("close_$day", '06:00'),
                'closes_next_day' => true,
            ];
        }

        $pairs = [
            'business_timezone' => $request->business_timezone,
            'google_timezone_lat' => $request->google_timezone_lat,
            'google_timezone_lng' => $request->google_timezone_lng,
            'add_to_order_minutes' => $request->add_to_order_minutes,
            'drive_in_discount_percent' => $request->drive_in_discount_percent,
            'whatsapp_number' => preg_replace('/\D+/', '', $request->whatsapp_number),
            'wholesale_delivery_start' => $request->wholesale_delivery_start,
            'wholesale_delivery_end' => $request->wholesale_delivery_end,
            'wholesale_cutoff_time' => $request->wholesale_cutoff_time,
            'wholesale_delivery_days' => json_encode($request->input('wholesale_delivery_days', ['monday', 'thursday', 'saturday'])),
            'opening_hours' => json_encode($hours),
            'private_booking_title' => $request->private_booking_title,
            'private_booking_description' => $request->private_booking_description,
            'app_download_text' => $request->app_download_text,
        ];

        foreach ($pairs as $key => $value) {
            BusinessSetting::setValue($key, $value);
        }

        if ($request->hasFile('private_booking_image')) {
            $file = $request->file('private_booking_image');
            $name = 'private-bookings.' . $file->getClientOriginalExtension();
            $file->move(public_path('img'), $name);
            BusinessSetting::setValue('private_booking_image', 'public/img/' . $name);
        }
        if ($request->hasFile('hero_video')) {
            $file = $request->file('hero_video');
            $name = 'hero.' . $file->getClientOriginalExtension();
            $dest = public_path('videos');
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, $name);
            BusinessSetting::setValue('hero_video_path', 'public/videos/' . $name);
        }
        if ($request->hasFile('hero_poster')) {
            $file = $request->file('hero_poster');
            $name = 'hero-poster.' . $file->getClientOriginalExtension();
            $file->move(public_path('img'), $name);
            BusinessSetting::setValue('hero_poster_path', 'public/img/' . $name);
        }

        Cache::forget('google_timezone_payload');
        return redirect()->back()->with(['status' => true, 'message' => 'Business settings updated.']);
    }
}
