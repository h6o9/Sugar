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
use Illuminate\Http\Request;
use App\Models\UserTimeSlotes;
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

    $products = Product::with(['variants','category','complementaryProduct.complementary'])
        ->where('status', 1)
        ->where('is_featured', 1)
        ->orderBy('id', 'DESC')
        ->get();

    // Set default price for products with variants
    foreach ($products as $product) {
        if ($product->variants && $product->variants->count() > 0) {
            // Try to find 'regular' variant first, otherwise use first variant with price
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

		$ciboExpressItems = \App\Models\CiboExpress::all();

    $menuCategories = Menu::with(['products' => function ($query) {
        $query->where('status', 1);
    }])->orderBy('id', 'asc')->get();

    foreach ($menuCategories as $menu) {

        $menuproduct = Product::where('menu_id', $menu->id)
            ->where('status', 1)
            ->with(['variants','complementaryProduct.complementary'])
            ->get();

        // Set default price for menu products with variants
        foreach ($menuproduct as $product) {
            if ($product->variants && $product->variants->count() > 0) {
                // Try to find 'regular' variant first, otherwise use first variant with price
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

        $menu->product = $menuproduct;
    }

    $faqs = Faq::orderBy('id', 'DESC')->get();
    $branches = Branch::all();

    $userId = Auth::guard('user')->id();

    $userTimeSlots = UserTimeSlotes::where('user_id', $userId)->first();

    $timeSlots = TimeSlot::all();

    $menuGalleries = MenuGallery::orderBy('id', 'DESC')->take(4)->get();
    // return $products;
    return view('home.index', compact(
        'products',
        'branches',
        'timeSlots',
        'menuGalleries',
        'userTimeSlots',
        'menuCategories',
        'faqs',
        'ciboExpressItems'
    ));
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
        $userTimeSlots = UserTimeSlotes::where('user_id', $userId)
            ->first();
        $timeSlots = TimeSlot::all();
        // $products = Menu::with('products.category')->orderBy('id', 'asc')->get();
        $products = Menu::with(['products.category','products' => function ($query) {
            $query->where('status', 1); // Filter products by status
        }])
        ->orderBy('id', 'asc')
        ->get();
        foreach ($products as $product) {
            $menuproduct = Product::where('menu_id', $product->id)->get();
            
            // Set default price for menu products with variants
            foreach ($menuproduct as $prod) {
                if ($prod->variants && $prod->variants->count() > 0) {
                    // Try to find 'regular' variant first, otherwise use first variant with price
                    $regularVariant = $prod->variants->where('size', 'regular')->first();
                    $firstVariantWithPrice = $prod->variants->where('price', '>', 0)->first();
                    
                    if ($regularVariant && $regularVariant->price > 0) {
                        $prod->default_price = $regularVariant->price;
                    } elseif ($firstVariantWithPrice) {
                        $prod->default_price = $firstVariantWithPrice->price;
                    } else {
                        $prod->default_price = $prod->variants->first()->price ?? 0;
                    }
                } else {
                    $prod->default_price = $prod->price ?? 0;
                }
            }
            
            $product->product = $menuproduct;
        }
        return view('home.our-menu', compact('products', 'branches', 'timeSlots', 'userTimeSlots'));
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
        $branches = Branch::all();
        $timeSlots = TimeSlot::all();
        $userId = Auth::guard('user')->id();
        $searchTerm = $request->input('search');
        $userTimeSlots = UserTimeSlotes::where('user_id', $userId)->first();

        // Filtered products matching the search term
        $filteredProducts = Product::with(['variants', 'category.getCategory'])
            ->where('status', 1)
            ->where('name', 'like', "%{$searchTerm}%")
            ->get();

        // Get menu categories (for consistent layout)
        $menuCategories = Menu::with(['products' => function ($query) {
            $query->where('status', 1);
        }])->orderBy('id', 'asc')->get();

        // Eager load products with variants for better performance
        foreach ($menuCategories as $menu) {
            $menu->product = $menu->products->load('variants');
            
            // Set default price for menu products with variants
            foreach ($menu->product as $product) {
                if ($product->variants && $product->variants->count() > 0) {
                    // Try to find 'regular' variant first, otherwise use first variant with price
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
        }

        // Menu galleries for top display
        $menuGalleries = MenuGallery::orderBy('id', 'DESC')->take(4)->get();

        return view('home.our-menu', compact(
            'filteredProducts',
            'branches',
            'timeSlots',
            'userTimeSlots',
            'menuCategories',
            'menuGalleries',
            'searchTerm'
        ));
    }


    public function sendMail(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
            // 'g-recaptcha-response' => 'required',
        ]);
        // return $request;
        $data = $request->all();
        Mail::to('contact@sugarpappi.com')->send(new ContactMail($data));
        return redirect()->back()->with(['status' => true, 'message' => 'Your message has been sent Successfully! We will get back to you soon.']);
    }
}
