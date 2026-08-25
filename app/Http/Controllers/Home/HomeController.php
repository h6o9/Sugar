<?php

namespace App\Http\Controllers\Home;

use App\Models\Faq;
use App\Models\Menu;
use App\Models\Branch;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Seamoss;
use App\Models\Topping;
use App\Models\TimeSlot;
use App\Rules\ReCaptcha;
use App\Mail\ContactMail;
use App\Models\MenuGallery;
use App\Http\Requests\AntiBotFormRequest;
use Illuminate\Http\Request;
use App\Models\UserTimeSlotes;
use App\Support\MenuCatalog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
public function index()
{
    $user = Auth::user();

    $products = Product::with(['variants', 'category', 'complementaryProductSingle.complementary'])
        ->where('status', 1)
        ->where('is_featured', 1)
        ->orderBy('id', 'DESC')
        ->get();

    // Set default price for products with variants
    foreach ($products as $product) {
        if ($product->variants && $product->variants->count() > 0) {
            $regularVariant = $product->variants->where('size', 'regular')->first();
            $firstVariantWithPrice = $product->variants->where('price', '>', 0)->first();

            if ($regularVariant && $regularVariant->price > 0) {
                $product->default_price = $regularVariant->price;
            } elseif ($firstVariantWithPrice) {
                $product->default_price = $firstVariantWithPrice->price;
            } else {
                $product->default_price = $product->variants->first()->price ?? 0;
            }
        } else {
            $product->default_price = $product->price ?? 0;
        }
    }

    $faqs = Faq::orderBy('id', 'DESC')->get();
    $branches = Branch::all();
    $ciboExpressItems = collect();
    $landingStores = $this->landingStores($branches);
    $pappiSpecial = $products;
    $heroVideo = 'public/videos/hero.mp4';
    $heroPoster = 'public/img/hero-poster.jpg';
    $hours = ['is_open' => true, 'message' => '', 'next_opening_time' => '4:00 PM'];
    try {
        $heroVideo = \App\Models\BusinessSetting::getValue('hero_video_path', $heroVideo);
        $heroPoster = \App\Models\BusinessSetting::getValue('hero_poster_path', $heroPoster);
        $hours = app(\App\Services\BusinessTimeService::class)->status();
    } catch (\Throwable $e) {
        \Log::warning('Home landing extras skipped: '.$e->getMessage());
    }

    $menuCategories = MenuCatalog::forStorefront(true, true);

    $userId = Auth::guard('user')->id();

    $userTimeSlots = UserTimeSlotes::where('user_id', $userId)->first();

    $timeSlots = TimeSlot::all();

    $menuGalleries = MenuGallery::orderBy('id', 'DESC')->take(4)->get();

    return view('home.index', compact(
        'products',
        'branches',
        'timeSlots',
        'menuGalleries',
        'userTimeSlots',
        'menuCategories',
        'faqs',
        'ciboExpressItems',
        'landingStores',
        'pappiSpecial',
        'heroVideo',
        'heroPoster',
        'hours'
    ));
}

    protected function landingStores($branches)
    {
        $stores = $branches->filter(function ($branch) {
            $orderable = (bool) ($branch->is_orderable ?? false);
            $hay = strtolower(($branch->city_label ?? '') . ' ' . ($branch->name ?? '') . ' ' . ($branch->location ?? ''));
            $manchester = strpos($hay, 'manchester') !== false;
            return $orderable || $manchester;
        })->map(function ($branch) {
            return [
                'label' => $branch->city_label ?: $branch->name,
                'branch' => $branch,
                'is_orderable' => true,
            ];
        })->values();

        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'S1',
            'location' => 'HomeController.php:landingStores',
            'message' => 'orderable stores for modal',
            'data' => ['count' => $stores->count(), 'labels' => $stores->pluck('label')->values()],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion

        return $stores;
    }

    public function getmenuPicture()
    {
        $menuGalleries = MenuGallery::orderBy('id', 'DESC')->get();
        return view('home.menu-picture', compact('menuGalleries'));
    }

    public function getOurMenu()
    {
        $branches = Branch::all();
        $userId = Auth::guard('user')->id();
        $userTimeSlots = UserTimeSlotes::where('user_id', $userId)->first();
        $timeSlots = TimeSlot::all();
        $searchTerm = '';
        $filteredProducts = collect();

        $menuCategories = MenuCatalog::forStorefront(true, true);

        $addingToOrder = false;
        try {
            $addingToOrder = app(\App\Services\OrderLifecycleService::class)
                ->hasActiveAddToOrderSession($userId ? (int) $userId : null);
        } catch (\Throwable $e) {
            $addingToOrder = false;
        }

        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'runId' => 'post-fix',
            'hypothesisId' => 'H3',
            'location' => 'HomeController.php:getOurMenu',
            'message' => 'menu categories for storefront',
            'data' => [
                'addingToOrder' => (bool) $addingToOrder,
                'categories' => $menuCategories->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'type' => $m->type ?? null,
                        'slug' => $m->slug ?? null,
                        'product_count' => $m->product ? $m->product->count() : 0,
                    ];
                })->values(),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion

        return view('home.our-menu', compact(
            'menuCategories',
            'branches',
            'timeSlots',
            'userTimeSlots',
            'searchTerm',
            'filteredProducts',
            'addingToOrder'
        ));
    }
    public function getOurGallery()
    {
        $galleries = Gallery::orderBy('id', 'DESC')->get();
        return view('home.our-gallery', compact('galleries'));
    }
    public function getNewSeaMoss()
    {
        $data = Seamoss::first();
        return view('home.new-sea-moss', compact('data'));
    }

  public function search(Request $request)
{
    $branches      = Branch::all();
    $timeSlots     = TimeSlot::all();
    $userId        = Auth::guard('user')->id();
    $searchTerm    = $request->input('search');
    $userTimeSlots = UserTimeSlotes::where('user_id', $userId)->first();

    // ✅ FIX: Load ALL relationships needed by calcDiscount() + modal rendering
    $filteredProducts = Product::with([
            'menu',
            'variants',
            'category.getCategory',
            'complementaryProductSingle.complementary.variants',
        ])
        ->where('status', 1)
        ->where('name', 'like', "%{$searchTerm}%")
        ->get()
        ->filter(function ($product) {
            return !MenuCatalog::isSpecial($product->menu);
        })
        ->values();

    // ✅ FIX: Set default_price, original_price, featured_* on each filtered product
    foreach ($filteredProducts as $product) {
        if ($product->variants && $product->variants->count() > 0) {

            // Pick display variant — prefer 'regular', else first with price > 0
            $regularVariant        = $product->variants->where('size', 'regular')->first();
            $firstVariantWithPrice = $product->variants->where('price', '>', 0)->first();

            if ($regularVariant && $regularVariant->price > 0) {
                $displayVariant = $regularVariant;
            } elseif ($firstVariantWithPrice) {
                $displayVariant = $firstVariantWithPrice;
            } else {
                $displayVariant = $product->variants->first();
            }

            // ✅ default_price = the display variant's current (discounted) price
            $product->default_price = $displayVariant ? $displayVariant->price : 0;

            // ✅ original_price for calcDiscount() strikethrough
            //    variant.original_price = base price before discount
            if ($displayVariant && isset($displayVariant->original_price) && $displayVariant->original_price > 0) {
                $product->original_price = $displayVariant->original_price;
            }
            // If no variant.original_price, calcDiscount() will compare product.original_price
            // against product.price — both already on the model from DB

        } else {
            $product->default_price = $product->price ?? 0;
            // product.original_price & product.price already loaded from DB
        }
    }

    $menuCategories = MenuCatalog::forStorefront(true, true);

    $menuGalleries = MenuGallery::orderBy('id', 'DESC')->take(4)->get();

    $addingToOrder = false;
    try {
        $addingToOrder = app(\App\Services\OrderLifecycleService::class)
            ->hasActiveAddToOrderSession($userId ? (int) $userId : null);
    } catch (\Throwable $e) {
        $addingToOrder = false;
    }

    return view('home.our-menu', compact(
        'filteredProducts',
        'branches',
        'timeSlots',
        'userTimeSlots',
        'menuCategories',
        'menuGalleries',
        'searchTerm',
        'addingToOrder'
    ));
}

    public function sendMail(AntiBotFormRequest $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
            'g-recaptcha-response' => 'required|recaptcha',
        ]);
        // return $request;
        $data = $request->all();
        Mail::to('contact@sugarpappi.com')->send(new ContactMail($data));
        return redirect()->back()->with(['status' => true, 'message' => 'Your message has been sent Successfully! We will get back to you soon.']);
    }
}
