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

    public function __construct(\App\Services\PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    public function index()
    {
        $cart = Auth::user()->cart()->with('items.product')->first();

        if (!$cart) {
            return $this->successResponse(['items' => []], 'السلة فارغة حالياً');
        }

        return $this->successResponse($cart, 'تم جلب بيانات السلة بنجاح');
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

        return $this->successResponse($cart->load('items.product'), 'تم إضافة المنتج إلى السلة بنجاح');
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse('لم يتم العثور على السلة', 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return $this->errorResponse('المنتج غير موجود في السلة', 404);
        }

        $product = $cartItem->product;
        $calculation = $this->priceCalculator->calculate($product, $request->quantity);

        $cartItem->quantity = $request->quantity;
        $cartItem->price_snapshot = $calculation['final_unit_price'];
        $cartItem->save();

        return $this->successResponse($cart->load('items.product'), 'تم تحديث كمية المنتج بنجاح');
    }

    public function destroy($cartItemId)
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return $this->errorResponse('لم يتم العثور على السلة', 404);
        }

        $cartItem = $cart->items()->where('id', $cartItemId)->first();

        if (!$cartItem) {
            return $this->errorResponse('المنتج غير موجود في السلة', 404);
        }

        $cartItem->delete();

        return $this->successResponse($cart->load('items.product'), 'تم إزالة المنتج من السلة بنجاح');
    }

    public function clear()
    {
        $user = Auth::user();
        $cart = $user->cart;

        if ($cart) {
            $cart->items()->delete();
        }

        return $this->successResponse(null, 'تم تفريغ السلة بنجاح');
    }
}
