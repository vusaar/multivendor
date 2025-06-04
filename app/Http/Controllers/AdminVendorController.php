<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminVendorController extends Controller
{
    // Show form to create a new vendor
    public function create()
    {

         /*
            if user has vendor.admin role
         */
            // Get only users with the vendor_admin role to select as administrator
        $users = \App\Models\User::role('vendor.admin')->get();
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

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('vendor_logos', 'public');
        }

        \App\Models\Vendor::create([
            'user_id' => $request->user_id,
            'shop_name' => $request->shop_name,
            'description' => $request->description,
            'logo' => $logoPath,
            'address' => $request->address,
            'status' => $request->status,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
        ]);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    // List all vendors
    public function index()
    {
        // If user has super.admin role, show all vendors
        if (auth()->user()->hasRole('super.admin')) {
            $vendors = Vendor::with('user')->paginate(10);
        }
        // If user has vendor.admin role, show only vendors associated with that admin
        elseif (auth()->user()->hasRole('vendor.admin')) {
            $vendors = Vendor::with('user')
                ->where('user_id', auth()->id())
                ->paginate(10);
        } else {
            // Optionally, restrict access for other roles
            abort(403, 'Unauthorized');
        }
        return view('admin.vendors.index', compact('vendors'));
    }

    // Show all vendors and their administrators with filtering and pagination
    public function show(Request $request)
    {
        $query = Vendor::with('user');
        if ($request->filled('shop_name')) {
            $query->where('shop_name', 'like', '%' . $request->shop_name . '%');
        }
        if ($request->filled('admin_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->admin_email . '%');
            });
        }
        if ($request->filled('address')) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }
        $vendors = $query->paginate($request->get('per_page', 10))->appends($request->all());
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
        $data = $request->only(['shop_name', 'description', 'address', 'status']);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('vendor_logos', 'public');
        }
        $vendor->update($data);
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    // Delete a vendor
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
