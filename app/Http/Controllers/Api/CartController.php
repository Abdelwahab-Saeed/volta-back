<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class CartController extends Controller
{
    use ApiResponse;

    protected $priceCalculator;
    protected $metaService;

    public function __construct(\App\Services\PriceCalculator $priceCalculator, \App\Services\MetaService $metaService)
    {
        $this->priceCalculator = $priceCalculator;
        $this->metaService = $metaService;
    }

    public function index()
    {
        $cart = Auth::user()->cart()->with('items.product')->first();

        if (!$cart) {
            return $this->successResponse(['items' => []], __('api.cart_empty'));
        }

        return $this->successResponse($cart, __('api.cart_fetched'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

        $cartItem = $cart->items()->where('product_id', $request->product_id)->first();

        $product = Product::find($request->product_id);
        
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            $cartItem->quantity = $newQuantity;
            
            // Recalculate price based on new total quantity
            $calculation = $this->priceCalculator->calculate($product, $newQuantity);
            $cartItem->price_snapshot = $calculation['final_unit_price']; 
            
            $cartItem->save();
        } else {
            // Calculate initial price
            $calculation = $this->priceCalculator->calculate($product, $request->quantity);
            
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price_snapshot' => $calculation['final_unit_price'],
            ]);
        }

        $this->metaService->sendAddToCart($product, $user);

        return $this->successResponse($cart->load('items.product'), __('api.cart_item_added'));
    }

    /**
     * Merge the cart a customer built as a guest (kept in the browser) into their account cart, right after login.
     * A product in both carts keeps the larger quantity instead of adding them up, so a retried or repeated
     * merge request never doubles quantities. Deleted or hidden products are skipped.
     * url: POST api/cart/merge  { items: [{ product_id, quantity }] }
     */
    public function merge(Request $request)
    {
        $request->validate([
            'items' => 'present|array|max:100',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
        ]);

        $user = Auth::user();
        $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

        // The same product can appear twice in a guest cart; take its largest quantity.
        $wanted = collect($request->items)
            ->groupBy('product_id')
            ->map(fn ($lines) => (int) collect($lines)->max('quantity'));

        $products = Product::where('status', true)->whereIn('id', $wanted->keys())->get()->keyBy('id');
        $existing = $cart->items()->whereIn('product_id', $products->keys())->get()->keyBy('product_id');

        foreach ($products as $productId => $product) {
            $quantity = max($wanted[$productId], $existing[$productId]->quantity ?? 0);
            $calculation = $this->priceCalculator->calculate($product, $quantity);

            $cart->items()->updateOrCreate(
                ['product_id' => $productId],
                ['quantity' => $quantity, 'price_snapshot' => $calculation['final_unit_price']]
            );
        }

        return $this->successResponse($cart->load('items.product'), __('api.cart_merged'));
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse(__('api.cart_not_found'), 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return $this->errorResponse(__('api.cart_item_not_found'), 404);
        }

        $product = $cartItem->product;
        $calculation = $this->priceCalculator->calculate($product, $request->quantity);

        $cartItem->quantity = $request->quantity;
        $cartItem->price_snapshot = $calculation['final_unit_price'];
        $cartItem->save();

        return $this->successResponse($cart->load('items.product'), __('api.cart_item_updated'));
    }

    public function destroy($cartItemId)
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse(__('api.cart_not_found'), 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return $this->errorResponse(__('api.cart_item_not_found'), 404);
        }

        $cartItem->delete();

        return $this->successResponse($cart->load('items.product'), __('api.cart_item_removed'));
    }

    public function clear()
    {
        $user = Auth::user();
        $cart = $user->cart;

        if ($cart) {
            $cart->items()->delete();
        }

        return $this->successResponse(null, __('api.cart_cleared'));
    }
}
