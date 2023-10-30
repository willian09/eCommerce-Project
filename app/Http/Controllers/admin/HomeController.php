<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();    
        
        $totalProducts = Product::count();
        
        $totalCategory = Category::count();
        
        $totalSubCategory = SubCategory::count();
        
        $totalBrands = Brand::count();
        
        $totalCostumer = User::where('role',2)->count();
        
        $totalSale = Order::where('status', '!=', 'cancelled')->sum('grand_total');

        // This month sale
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $currenteDate = Carbon::now()->format('Y-m-d');

        $saleThisMonth = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $startOfMonth)
                            ->whereDate('created_at', '<=', $currenteDate)
                            ->sum('grand_total');

        // This month sale    
        $saleLastMonthStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $saleLastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->format('M');

        $saleLastMonth = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $saleLastMonthStart)
                            ->whereDate('created_at', '<=', $saleLastMonthEnd)
                            ->sum('grand_total');
        
        // This 30 days sale
        $startDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $saleLastThirtyDays = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $currenteDate)
                            ->sum('grand_total');

        
        return view('admin.dashboard', [
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalCategory' => $totalCategory,
            'totalSubCategory' => $totalSubCategory,
            'totalBrands' => $totalBrands,
            'totalCostumer' => $totalCostumer,
            'totalSale' => $totalSale,
            'saleThisMonth' => $saleThisMonth,
            'saleLastMonth' => $saleLastMonth,
            'saleLastThirtyDays' => $saleLastThirtyDays,
            'lastMonth' => $lastMonth
        ]);
        //$admin = Auth::guard('admin')->user();
        //echo 'Welcome ' . $admin->name . ' <a href="' . route('admin.logout') . '">Logout</a>';
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');                    
    }
}
