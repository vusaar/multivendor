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
        // Get only users with the vendor_admin role to select as administrator
        $users = User::role('vendor.admin')->get();
        return view('admin.vendors.create', compact('users'));
    }

    // Store a new vendor and associate an administrator
    public function store(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'user_id' => 'required|exists:users,id',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
        ]);

        $this->vendorService->createVendor($request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
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
        return view('admin.vendors.edit', compact('vendor'));
    }

    // Update a vendor
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $this->vendorService->updateVendor($vendor, $request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    // Delete a vendor
    public function destroy(Vendor $vendor)
    {
        $this->vendorService->deleteVendor($vendor);
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
