<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles
        \Spatie\Permission\Models\Role::create(['name' => 'super.admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'vendor.admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super.admin');
    }

    /** @test */
    public function an_admin_can_view_vendors_list()
    {
        Vendor::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.vendors.index'));

        $response->assertStatus(200);
        $response->assertViewHas('vendors');
    }

    /** @test */
    public function an_admin_can_create_a_vendor()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $data = [
            'shop_name' => 'Test Shop',
            'description' => 'Test Description',
            'status' => 'approved',
            'user_id' => $user->id,
            'address' => '123 Test St',
            'logo' => UploadedFile::fake()->create('logo.jpg', 100)
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.vendors.store'), $data);

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseHas('vendors', ['shop_name' => 'Test Shop']);
        
        $vendor = Vendor::where('shop_name', 'Test Shop')->first();
        Storage::disk('public')->assertExists($vendor->logo);
    }

    /** @test */
    public function an_admin_can_update_a_vendor()
    {
        Storage::fake('public');
        $vendor = Vendor::factory()->create();

        $data = [
            'shop_name' => 'Updated Shop',
            'status' => 'pending',
            'logo' => UploadedFile::fake()->create('new_logo.jpg', 100),
            // other required fields if any
            'description' => $vendor->description,
            'address' => $vendor->address
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.vendors.update', $vendor), $data);

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'shop_name' => 'Updated Shop']);
        
        $vendor->refresh();
        Storage::disk('public')->assertExists($vendor->logo);
    }

    /** @test */
    public function an_admin_can_delete_a_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.vendors.destroy', $vendor));

        $response->assertRedirect(route('admin.vendors.index'));
        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }
}
