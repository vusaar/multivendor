<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super.admin');
        $isVendorAdmin = $user->hasRole('vendor.admin');

        $stats = [
            'totalProducts' => 0,
            'activeVendors' => 0,
            'totalCategories' => 0,
            'isSuperAdmin' => $isSuperAdmin,
        ];

        if ($isSuperAdmin) {
            $stats['totalProducts'] = Product::count();
            $stats['activeVendors'] = Vendor::where('status', 'approved')->count();
            $stats['totalCategories'] = Category::where('status', 'approved')->count();
        } elseif ($isVendorAdmin) {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if ($vendor) {
                $productIds = Product::where('vendor_id', $vendor->id)->pluck('id')->toArray();
                
                $stats['totalProducts'] = count($productIds);
                $stats['totalCategories'] = Product::where('vendor_id', $vendor->id)
                    ->distinct('category_id')
                    ->count('category_id');
                
                // Calculate Search Appearances (Total, Top 5, Top 10)
                $stats['searchAppearances'] = 0;
                $stats['top5Appearances'] = 0;
                $stats['top10Appearances'] = 0;

                if (!empty($productIds)) {
                    // Fetch recent logs for analysis (limit to 1000 for performance)
                    $logs = \App\Models\SearchLog::whereNotNull('results')
                        ->orderBy('id', 'desc')
                        ->limit(1000)
                        ->get();

                    foreach ($logs as $log) {
                        $results = $log->results;
                        $isInTotal = false;
                        $isInTop5 = false;
                        $isInTop10 = false;

                        foreach ($results as $index => $item) {
                            if (in_array($item['id'], $productIds)) {
                                $isInTotal = true;
                                if ($index < 5) $isInTop5 = true;
                                if ($index < 10) $isInTop10 = true;
                                break; // Count once per search
                            }
                        }

                        if ($isInTotal) $stats['searchAppearances']++;
                        if ($isInTop5) $stats['top5Appearances']++;
                        if ($isInTop10) $stats['top10Appearances']++;
                    }
                }

                $stats['vendorStatus'] = ucfirst($vendor->status);
                $stats['shopName'] = $vendor->shop_name;
            }
        }

        return view('dashboard', compact('stats'));
    }
}
