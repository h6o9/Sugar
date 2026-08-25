<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        $defaults = [
            'business_timezone' => 'Europe/London',
            'google_timezone_lat' => '53.4808',
            'google_timezone_lng' => '-2.2426',
            'opening_hours' => json_encode([
                'monday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'tuesday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'wednesday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'thursday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'friday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'saturday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
                'sunday' => ['open' => '14:00', 'close' => '06:00', 'closes_next_day' => true],
            ]),
            'add_to_order_minutes' => '10',
            'wholesale_delivery_days' => json_encode(['monday', 'thursday', 'saturday']),
            'wholesale_delivery_start' => '19:00',
            'wholesale_delivery_end' => '22:00',
            'wholesale_cutoff_time' => '19:00',
            'drive_in_discount_percent' => '20',
            'whatsapp_number' => '447727412922',
            'hero_video_path' => 'public/videos/hero.mp4',
            'hero_poster_path' => 'public/img/hero-poster.jpg',
            'private_booking_title' => 'Private Bookings / Large Orders',
            'private_booking_description' => 'Book Sugar Pappi for private events and large orders. Our dessert station can be set up for weddings, parties and corporate events.',
            'private_booking_image' => 'public/img/private-bookings.jpg',
            'landing_rating' => '4.6',
            'landing_rating_count' => '2000+',
            'landing_locations_label' => '4 LOCATIONS',
            'app_download_text' => 'DOWNLOAD THE APP FOR SAVINGS & DISCOUNTS',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            $exists = DB::table('business_settings')->where('key', $key)->exists();
            if (!$exists) {
                DB::table('business_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
