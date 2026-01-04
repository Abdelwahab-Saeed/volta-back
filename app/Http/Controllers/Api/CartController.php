<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart()->with('items.product')->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty', 'items' => []]);
        }

        return response()->json($cart);
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

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            // Optionally update price snapshot to current price when adding more
            $product = Product::find($request->product_id);
            $cartItem->price_snapshot = $product->final_price; 
            $cartItem->save();
        } else {
            $product = Product::find($request->product_id);
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price_snapshot' => $product->final_price,
            ]);
        }

        return response()->json(['message' => 'Item added to cart', 'cart' => $cart->load('items.product')]);
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }

        $cartItem->quantity = $request->quantity;
        // Refresh price snapshot to current final price
        $cartItem->price_snapshot = $cartItem->product->final_price;
        $cartItem->save();

        return response()->json(['message' => 'Cart updated', 'cart' => $cart->load('items.product')]);
    }

    public function destroy($cartItemId)
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in cart'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart', 'cart' => $cart->load('items.product')]);
    }

    public function clear()
    {
        $user = Auth::user();
        $cart = $user->cart;

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Cart cleared']);
    }
}
