<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Services\PriceCalculator;
use Database\Seeders\Support\MockData;
use Illuminate\Database\Seeder;

/**
 * Carts, wishlists and comparison lists for the existing customers.
 */
class ShoppingActivitySeeder extends Seeder
{
    public function __construct(private PriceCalculator $calculator)
    {
    }

    public function run(): void
    {
        $products = Product::where('status', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        $inStock = $products->where('stock', '>', 0);

        foreach (MockData::customers() as $customer) {
            if ($inStock->isNotEmpty() && fake()->boolean(70)) {
                $cart = Cart::firstOrCreate(['user_id' => $customer->id]);

                foreach ($inStock->random(min(random_int(1, 3), $inStock->count())) as $product) {
                    $quantity = random_int(1, 2);

                    $cart->items()->updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'quantity' => $quantity,
                            'price_snapshot' => $this->calculator->calculate($product, $quantity)['final_unit_price'],
                        ],
                    );
                }
            }

            $customer->wishlist()->syncWithoutDetaching(
                $products->random(min(random_int(0, 5), $products->count()))->pluck('id')
            );

            // Comparisons make sense within one category.
            $customer->comparisonList()->syncWithoutDetaching(
                $products->groupBy('category_id')->random()->shuffle()->take(random_int(0, 3))->pluck('id')
            );
        }
    }
}
