<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CommonCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Electronics' => [
                'Mobile Phones', 'Computers & Accessories', 'Cameras', 'Audio', 'Wearables', 'TV & Video'
            ],
            'Fashion' => [
                'Men', 'Women', 'Kids', 'Shoes', 'Bags', 'Accessories'
            ],
            'Home & Garden' => [
                'Furniture', 'Kitchen', 'Bedding', 'Garden', 'Home Decor', 'Appliances'
            ],
            'Health & Beauty' => [
                'Personal Care', 'Makeup', 'Skincare', 'Hair Care', 'Fragrances', 'Health Equipment'
            ],
            'Sports & Outdoors' => [
                'Exercise & Fitness', 'Outdoor Recreation', 'Team Sports', 'Cycling', 'Camping & Hiking'
            ],
            'Toys & Games' => [
                'Action Figures', 'Board Games', 'Dolls', 'Educational', 'Puzzles', 'Outdoor Play'
            ],
            'Automotive' => [
                'Car Electronics', 'Car Accessories', 'Motorcycle Parts', 'Tools & Equipment'
            ],
            'Groceries' => [
                'Beverages', 'Snacks', 'Pantry', 'Fresh Produce', 'Dairy', 'Bakery'
            ],
            'Books & Media' => [
                'Books', 'Magazines', 'Music', 'Movies', 'eBooks'
            ],
            'Office & School Supplies' => [
                'Stationery', 'Office Electronics', 'School Supplies', 'Art Supplies'
            ],
            'Jewelry & Watches' => [
                'Men', 'Women', 'Kids', 'Watches', 'Fine Jewelry', 'Fashion Jewelry'
            ],
            'Baby & Kids' => [
                'Baby Gear', 'Feeding', 'Nursery', 'Toys', 'Clothing', 'Bath & Potty'
            ],
            'Musical Instruments' => [
                'Guitars', 'Keyboards', 'Drums', 'Wind Instruments', 'Accessories'
            ],
            'Art & Collectibles' => [
                'Paintings', 'Sculptures', 'Collectible Figures', 'Prints', 'Handmade'
            ],
            'Industrial & Scientific' => [
                'Lab Equipment', 'Industrial Tools', 'Safety Supplies', 'Janitorial'
            ],
            'Travel & Luggage' => [
                'Luggage', 'Travel Accessories', 'Backpacks', 'Suitcases'
            ],
            'Tools & Hardware' => [
                'Power Tools', 'Hand Tools', 'Hardware', 'Building Supplies'
            ],
        ];

        foreach ($categories as $parent => $subs) {
            $parentCategory = Category::updateOrCreate(
                ['name' => $parent, 'parent_id' => null],
                ['name' => $parent, 'parent_id' => null]
            );
            foreach ($subs as $sub) {
                Category::updateOrCreate(
                    ['name' => $sub, 'parent_id' => $parentCategory->id],
                    ['name' => $sub, 'parent_id' => $parentCategory->id]
                );
            }
        }
    }
}
