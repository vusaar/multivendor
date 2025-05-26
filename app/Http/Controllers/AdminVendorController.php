<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminVendorController extends Controller
{
    // Show form to create a new vendor and administrator
    public function create()
    {
        return view('admin.vendors.create');
    }

    // Store a new vendor and associate an administrator
    public function store(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request) {
            $vendor = Vendor::create([
                'name' => $request->vendor_name,
                // Add other vendor fields as needed
            ]);

            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                // Add other user fields as needed
            ]);

            // Associate admin to vendor (assuming a vendor_id foreign key on users table)
            $admin->vendor_id = $vendor->id;
            $admin->save();

            // Optionally assign admin role
            if (method_exists($admin, 'assignRole')) {
                $admin->assignRole('vendor_admin');
            }
        });

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor and administrator created successfully.');
    }

    // Show all vendors and their administrators with filtering and pagination
    public function show(Request $request)
    {
        $query = Vendor::with('users');

        // Filtering
        if ($request->filled('vendor_name')) {
            $query->where('name', 'like', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('admin_email')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->admin_email . '%');
            });
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Pagination (default 10 per page)
        $vendors = $query->paginate($request->get('per_page', 10))->appends($request->all());

        if ($request->wantsJson()) {
            return response()->json($vendors);
        }

        return view('admin.vendors.show', compact('vendors'));
    }
}
