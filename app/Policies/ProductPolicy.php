<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product)
    {
        // For now, anyone can view active products, 
        // but vendor admins can always view their own products.
        return true;
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user)
    {
        return $user->hasRole('vendor.admin');
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product)
    {
        return $user->id === $product->vendor->user_id;
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product)
    {
        return $user->id === $product->vendor->user_id;
    }
}
