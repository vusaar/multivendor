<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $vendorAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        \Spatie\Permission\Models\Role::create(['name' => 'super.admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'vendor.admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super.admin');

        $this->vendorAdmin = User::factory()->create();
        $this->vendorAdmin->assignRole('vendor.admin');
    }

    /** @test */
    public function super_admin_can_view_all_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $this->assertCount(5, $response->viewData('products'));
    }

    /** @test */
    public function vendor_admin_only_sees_their_own_products()
    {
        $vendor = Vendor::factory()->create(['user_id' => $this->vendorAdmin->id]);
        $otherVendor = Vendor::factory()->create();

        Product::factory()->count(3)->create(['vendor_id' => $vendor->id]);
        Product::factory()->count(2)->create(['vendor_id' => $otherVendor->id]);

        $response = $this->actingAs($this->vendorAdmin)
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $this->assertCount(3, $response->viewData('products'));
    }

    /** @test */
    public function products_can_be_filtered_by_search_term()
    {
        Product::factory()->create(['name' => 'FindMe']);
        Product::factory()->create(['name' => 'Other']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index', ['search' => 'FindMe']));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('products'));
        $this->assertEquals('FindMe', $response->viewData('products')->first()->name);
    }
}
