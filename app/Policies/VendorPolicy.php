<?php

namespace App\Policies;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendorPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('super.admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create vendors.
     */
    public function create(User $user)
    {
        return false; // Handled by 'before' for super.admin
    }

    /**
     * Determine whether the user can view the vendor.
     */
    public function view(User $user, Vendor $vendor)
    {
        return $user->id === $vendor->user_id;
    }

    /**
     * Determine whether the user can update the vendor.
     */
    public function update(User $user, Vendor $vendor)
    {
        return $user->id === $vendor->user_id;
    }

    /**
     * Determine whether the user can delete the vendor.
     */
    public function delete(User $user, Vendor $vendor)
    {
        return false; // Even vendors cannot delete their own store
    }
}
