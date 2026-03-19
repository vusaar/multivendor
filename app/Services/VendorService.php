<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorService
{
    /**
     * Get paginated and filtered vendors.
     */
    public function getFilteredVendors(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Vendor::with('user');

        if (!empty($filters['shop_name'])) {
            $query->where('shop_name', 'like', '%' . $filters['shop_name'] . '%');
        }

        if (!empty($filters['admin_email'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('email', 'like', '%' . $filters['admin_email'] . '%');
            });
        }

        if (!empty($filters['address'])) {
            $query->where('address', 'like', '%' . $filters['address'] . '%');
        }

        // Role-based filtering handled in controller for now, or could be passed here.
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new vendor.
     */
    public function createVendor(array $data): Vendor
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $data['logo']->store('vendor_logos', 'public');
        }

        return Vendor::create($data);
    }

    /**
     * Update an existing vendor.
     */
    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old logo if exists
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $data['logo'] = $data['logo']->store('vendor_logos', 'public');
        }

        $vendor->update($data);
        return $vendor;
    }

    /**
     * Delete a vendor.
     */
    public function deleteVendor(Vendor $vendor): bool
    {
        if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
            Storage::disk('public')->delete($vendor->logo);
        }
        return $vendor->delete();
    }
}
