<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
class ProductService
{
    /**
     * Get paginated and filtered products.
     */
    public function getFilteredProducts(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::with(['vendor', 'category', 'images', 'brand']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Handle vendor specific access
        if (isset($filters['vendor_ids'])) {
            $query->whereIn('vendor_id', $filters['vendor_ids']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @param array $images
     * @param array $variationMatrix
     * @return Product
     * @throws \Exception
     */
    public function createProduct(array $data, array $images = [], array $variationMatrix = []): Product
    {
        DB::beginTransaction();
        try {
            $productData = collect($data)->only([
                'vendor_id', 'category_id', 'brand_id', 'name', 'description', 'price', 'stock', 'status'
            ])->toArray();

            $product = Product::create($productData);

            if (!empty($images)) {
                $this->storeImages($product, $images);
            }

            if (!empty($variationMatrix)) {
                $this->storeVariations($product, $variationMatrix);
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @param array $existingImageIds
     * @param array $newImages
     * @param array $variations
     * @return Product
     * @throws \Exception
     */
    public function updateProduct(Product $product, array $data, array $existingImageIds = [], array $newImages = [], array $variations = []): Product
    {
        DB::beginTransaction();
        try {
            $updateData = collect($data)->only([
                'vendor_id', 'category_id', 'brand_id', 'name', 'description', 'price', 'stock', 'status'
            ])->toArray();

            $product->update($updateData);

            $this->syncImages($product, $existingImageIds, $newImages);

            if (!empty($variations)) {
                $this->syncVariations($product, $variations);
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Store images for a product.
     */
    private function storeImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $path = $image->store('product_images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }
    }

    /**
     * Sync images during an update.
     */
    private function syncImages(Product $product, array $keepImageIds, array $newImages): void
    {
        $imagesToDelete = $product->images()->whereNotIn('id', $keepImageIds)->get();
        foreach ($imagesToDelete as $img) {
            if (Storage::disk('public')->exists($img->image)) {
                Storage::disk('public')->delete($img->image);
            }
            $img->delete();
        }

        if (!empty($newImages)) {
            $this->storeImages($product, $newImages);
        }
    }

    /**
     * Store new variations matrix.
     */
    private function storeVariations(Product $product, array $variationMatrix): void
    {
        foreach ($variationMatrix as $matrix) {
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'sku' => $matrix['sku'] ?? null,
                'price' => $matrix['price'] ?? null,
                'stock' => $matrix['stock'] ?? 0,
            ]);

            if (isset($matrix['image']) && $matrix['image'] instanceof UploadedFile) {
                $variation_image_path = $matrix['image']->store('variation_images', 'public');
                ProductVariationImage::create([
                    'product_variation_id' => $variation->id,
                    'image_path' => $variation_image_path,
                    'alt_text' => $matrix['sku'] ?? null,
                ]);
            }

            $attrValueIds = $this->extractAttributeValuesIds($matrix['attributes'] ?? []);
            if (!empty($attrValueIds)) {
                $variation->attributeValues()->sync($attrValueIds);
            }
        }
    }

    /**
     * Sync variations matrix during an update.
     */
    private function syncVariations(Product $product, array $submittedVariations): void
    {
        $keepVariationIds = [];

        foreach ($submittedVariations as $variationData) {
            $attrValueIds = $this->extractAttributeValuesIds($variationData['attributes'] ?? []);
            if (empty($attrValueIds)) continue;

            if (!empty($variationData['id'])) {
                $productVariation = ProductVariation::find($variationData['id']);
                if ($productVariation) {
                    $productVariation->update([
                        'sku' => $variationData['sku'] ?? null,
                        'price' => $variationData['price'] ?? $product->price,
                        'stock' => $variationData['stock'] ?? $product->stock,
                    ]);
                } else {
                    $productVariation = $this->createVariation($product, $variationData);
                }
            } else {
                $productVariation = $this->createVariation($product, $variationData);
            }

            $productVariation->attributeValues()->sync($attrValueIds);
            $keepVariationIds[] = $productVariation->id;

            if (isset($variationData['image']) && $variationData['image'] instanceof UploadedFile) {
                $this->syncVariationImage($productVariation, $variationData['image'], $variationData['sku'] ?? null);
            }
        }

        // Delete removed variations
        $product->variations()->whereNotIn('id', $keepVariationIds)->delete();
    }

    private function createVariation(Product $product, array $data): ProductVariation
    {
        return ProductVariation::create([
            'product_id' => $product->id,
            'sku' => $data['sku'] ?? null,
            'price' => $data['price'] ?? $product->price,
            'stock' => $data['stock'] ?? $product->stock,
        ]);
    }

    private function syncVariationImage(ProductVariation $variation, UploadedFile $newImage, ?string $sku): void
    {
        $oldImages = $variation->variationImages;
        if ($oldImages) {
            foreach ($oldImages as $oldImage) {
                if (Storage::disk('public')->exists($oldImage->image_path)) {
                    Storage::disk('public')->delete($oldImage->image_path);
                }
                $oldImage->delete();
            }
        }

        $variationImagePath = $newImage->store('variation_images', 'public');
        ProductVariationImage::create([
            'product_variation_id' => $variation->id,
            'image_path' => $variationImagePath,
            'alt_text' => $sku,
        ]);
    }

    private function extractAttributeValuesIds(array $attributes): array
    {
        $attrValueIds = [];
        foreach ($attributes as $pair) {
            if (empty($pair['attribute_id']) || empty($pair['value_id']) || !is_array($pair['value_id'])) continue;
            foreach ($pair['value_id'] as $valId) {
                if (!$valId) continue;
                $attrValueIds[] = $valId;
            }
        }
        return $attrValueIds;
    }
}
