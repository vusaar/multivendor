<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use App\Services\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminVendorController extends Controller
{
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    // Show form to create a new vendor
    public function create()
    {
        $this->authorize('create', Vendor::class);
        // Get only users with the vendor_admin role to select as administrator
        $users = User::role('vendor.admin')->get();
        return view('admin.vendors.create', compact('users'));
    }

    // Store a new vendor and associate an administrator
    public function store(Request $request)
    {
        $this->authorize('create', Vendor::class);
        $rules = [
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
        ];

        if ($request->create_new_user) {
            $rules['new_admin_name'] = 'required|string|max:255';
            $rules['new_admin_email'] = 'required|email|unique:users,email';
            $rules['new_admin_password'] = 'required|string|min:8';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $data = $request->all();

            if ($request->create_new_user) {
                $user = User::create([
                    'name' => $request->new_admin_name,
                    'email' => $request->new_admin_email,
                    'password' => Hash::make($request->new_admin_password),
                ]);

                // Clear login session if needed or just assign role
                $user->assignRole('vendor.admin');
                $data['user_id'] = $user->id;
            }

            $this->vendorService->createVendor($data);

            DB::commit();
            return redirect()->route('admin.vendors.index')->with('success', 'Vendor and Admin User created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating vendor: ' . $e->getMessage());
        }
    }

    // List all vendors
    public function index()
    {
        $filters = [];
        // If user has vendor.admin role, show only vendors associated with that admin
        if (auth()->user()->hasRole('vendor.admin')) {
            $filters['user_id'] = auth()->id();
        } elseif (!auth()->user()->hasRole('super.admin')) {
            abort(403, 'Unauthorized');
        }

        $vendors = $this->vendorService->getFilteredVendors($filters);

        return view('admin.vendors.index', compact('vendors'));
    }

    // Show all vendors and their administrators with filtering and pagination
    public function show(Request $request)
    {
        $filters = $request->only(['shop_name', 'admin_email', 'address']);
        $vendors = $this->vendorService->getFilteredVendors($filters, $request->get('per_page', 10));
        
        $vendors->appends($request->all());

        if ($request->wantsJson()) {
            return response()->json($vendors);
        }
        return view('admin.vendors.show', compact('vendors'));
    }

    // Show form to edit a vendor
    public function edit(Vendor $vendor)
    {
        $this->authorize('update', $vendor);
        // Include users with the role OR the current administrator (to ensure preselection works)
        $users = User::role('vendor.admin')
            ->orWhere('id', $vendor->user_id)
            ->get();
        return view('admin.vendors.edit', compact('vendor', 'users'));
    }

    // Update a vendor
    public function update(Request $request, Vendor $vendor)
    {
        $this->authorize('update', $vendor);

        $data = $request->all();

        // Security: Only Super Admin can change status or owner
        if (!auth()->user()->hasRole('super.admin')) {
            unset($data['status']);
            unset($data['user_id']);
        }

        $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:255',
            'status' => auth()->user()->hasRole('super.admin') ? 'required|in:pending,approved,rejected' : 'nullable',
            'user_id' => auth()->user()->hasRole('super.admin') ? 'required|exists:users,id' : 'nullable',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
        ]);

        $this->vendorService->updateVendor($vendor, $data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    // Delete a vendor
    public function destroy(Vendor $vendor)
    {
        $this->authorize('delete', $vendor);
        $this->vendorService->deleteVendor($vendor);
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Display search insights and keyword performance for a vendor.
     */
    public function searchInsights(Request $request)
    {
        // 1. Identify Target Vendor(s)
        $vendorId = $request->get('vendor_id');
        if (auth()->user()->hasRole('vendor.admin')) {
            $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();
            $vendorId = $vendor->id;
        } elseif ($vendorId) {
            $vendor = Vendor::findOrFail($vendorId);
        } else {
            // Super Admin global view - find first active or just pick first
            $vendor = Vendor::first();
            if (!$vendor) abort(404, 'No vendors found.');
            $vendorId = $vendor->id;
        }

        // 2. Get Product IDs for this vendor
        $products = \App\Models\Product::where('vendor_id', $vendorId)->get(['id', 'name']);
        $productIds = $products->pluck('id')->toArray();
        $productNames = $products->pluck('name', 'id')->toArray();

        if (empty($productIds)) {
            return view('admin.vendors.search_insights', [
                'vendor' => $vendor,
                'sortedInsights' => collect([]),
                'topKeywords' => collect([]),
                'totalAppearances' => 0,
                'productNames' => []
            ]);
        }

        // 3. Query Search Logs identifying hits for this vendor's products
        $idsString = implode(',', $productIds);
        
        $logs = \App\Models\SearchLog::whereRaw("
            EXISTS (
                SELECT 1 FROM jsonb_array_elements(results) AS r 
                WHERE (r->>'id')::int IN ($idsString)
            )
        ")
        ->where('created_at', '>=', now()->subDays(30))
        ->get();

        // 4. Process Aggregations
        $insights = [];
        $totalAppearances = $logs->count();

        foreach ($logs as $log) {
            $queryKey = strtolower($log->corrected_query ?: $log->query);
            if (!isset($insights[$queryKey])) {
                $insights[$queryKey] = [
                    'query' => $queryKey,
                    'count' => 0,
                    'total_rank' => 0,
                    'products' => [], 
                ];
            }

            $insights[$queryKey]['count']++;
            
            $bestRank = 999;
            foreach ($log->results as $index => $result) {
                if (in_array((int)$result['id'], $productIds)) {
                    $rank = $index + 1;
                    if ($rank < $bestRank) $bestRank = $rank;
                    
                    $pid = (int)$result['id'];
                    $insights[$queryKey]['products'][$pid] = ($insights[$queryKey]['products'][$pid] ?? 0) + 1;
                }
            }
            $insights[$queryKey]['total_rank'] += ($bestRank == 999 ? 10 : $bestRank);
        }

        foreach ($insights as &$data) {
            $data['avg_rank'] = round($data['total_rank'] / $data['count'], 1);
            arsort($data['products']);
            $data['top_product_ids'] = array_slice(array_keys($data['products']), 0, 2, true);
        }

        $sortedInsights = collect($insights)->sortByDesc('count')->values();
        $topKeywords = $sortedInsights->take(5);

        return view('admin.vendors.search_insights', compact('vendor', 'sortedInsights', 'topKeywords', 'totalAppearances', 'productNames'));
    }
}
