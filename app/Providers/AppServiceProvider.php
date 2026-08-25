<?php

namespace App\Providers;

use App\Models\Menu;
use App\Services\BusinessTimeService;
use App\Services\OrderLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
       error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

       view()->composer(['home.*', 'home.common.*'], function ($view) {
            $shared = [
                'businessHours' => ['is_open' => true, 'message' => '', 'next_opening_time' => '4:00 PM'],
                'activeOrderState' => ['can_add_items' => false],
                'navMenus' => collect(),
                'whatsappUrl' => 'https://wa.me/447727412922',
                'whatsappNumber' => '447727412922',
            ];

            try {
                $orders = app(OrderLifecycleService::class);
                $userId = Auth::guard('user')->id();
                $active = $orders->activeModifiableOrder($userId ? (int) $userId : null);
                $shared['activeOrderState'] = $orders->publicState($active);
            } catch (\Throwable $e) {
                Log::warning('activeOrderState composer failed', ['error' => $e->getMessage()]);
            }

            try {
                $shared['businessHours'] = app(BusinessTimeService::class)->status();
            } catch (\Throwable $e) {
                Log::warning('businessHours composer failed', ['error' => $e->getMessage()]);
            }

            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('menus', 'sort_order')) {
                    $shared['navMenus'] = Menu::query()->orderByRaw('COALESCE(sort_order, id) asc')->get();
                } else {
                    $shared['navMenus'] = Menu::query()->orderBy('id')->get();
                }
            } catch (\Throwable $e) {
                try {
                    $shared['navMenus'] = Menu::query()->orderBy('id')->get();
                } catch (\Throwable $inner) {
                    $shared['navMenus'] = collect();
                }
            }

            $shared['pappiSpecialMenu'] = $shared['navMenus']->first(function ($m) {
                $type = strtolower((string) ($m->type ?? ''));
                $slug = strtolower((string) ($m->slug ?? ''));
                return $type === 'special' || $slug === 'pappi-special' || stripos((string) $m->name, 'Pappi Special') !== false;
            });

            try {
                $whatsapp = preg_replace('/\D+/', '', \App\Models\BusinessSetting::getValue('whatsapp_number', '447727412922'));
                $shared['whatsappUrl'] = 'https://wa.me/' . $whatsapp;
                $shared['whatsappNumber'] = $whatsapp;
            } catch (\Throwable $e) {
            }

            $view->with($shared);
       });
    }
}
