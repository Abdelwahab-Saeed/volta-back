<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image',
        'type',
        'value',
        'buy_quantity',
        'get_quantity',
        'get_product_id',
        'min_spend',
        'discount_amount',
        'bundle_price',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value'           => 'decimal:2',
        'min_spend'       => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'bundle_price'    => 'decimal:2',
        'is_active'       => 'boolean',
        'starts_at'       => 'datetime',
        'expires_at'      => 'datetime',
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'get_product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        return true;
    }

    /**
     * Calculate how much discount this offer applies to the given cart items.
     *
     * @param  \Illuminate\Support\Collection  $items   Each item must have: product, quantity, total_line_price
     * @param  float                           $subtotal
     * @return array ['discount' => float, 'free_items' => array]
     */
    public function calculateDiscount($items, float $subtotal): array
    {
        $offerProductIds = $this->products->pluck('id')->toArray();
        $freeItems = [];

        switch ($this->type) {

            // ── 1. Percentage off each qualifying product ──────────────────
            case 'percentage':
                $discount = 0;
                foreach ($items as $item) {
                    if (empty($offerProductIds) || in_array($item->product_id, $offerProductIds)) {
                        $discount += ($item->total_line_price * ($this->value / 100));
                    }
                }
                return ['discount' => round($discount, 2), 'free_items' => []];

            // ── 2. Fixed amount off each qualifying product ────────────────
            case 'fixed':
                $discount = 0;
                foreach ($items as $item) {
                    if (empty($offerProductIds) || in_array($item->product_id, $offerProductIds)) {
                        $discount += min($this->value * $item->quantity, $item->total_line_price);
                    }
                }
                return ['discount' => round($discount, 2), 'free_items' => []];

            // ── 3. Bundle — fixed total price for ALL listed products ──────
            case 'bundle':
                // All bundle products must be in the cart
                $bundleProductIds = $offerProductIds;
                $cartProductIds   = $items->pluck('product_id')->toArray();
                $allPresent       = count(array_intersect($bundleProductIds, $cartProductIds)) === count($bundleProductIds);

                if ($allPresent && $this->bundle_price !== null) {
                    // Sum of prices for the bundle products in cart
                    $bundleSubtotal = 0;
                    foreach ($items as $item) {
                        if (in_array($item->product_id, $bundleProductIds)) {
                            $bundleSubtotal += $item->total_line_price;
                        }
                    }
                    $discount = max(0, $bundleSubtotal - $this->bundle_price);
                    return ['discount' => round($discount, 2), 'free_items' => []];
                }
                return ['discount' => 0, 'free_items' => []];

            // ── 4. Buy X get Y free ────────────────────────────────────────
            case 'buy_x_get_y':
                $discount = 0;
                foreach ($items as $item) {
                    if (empty($offerProductIds) || in_array($item->product_id, $offerProductIds)) {
                        if ($item->quantity >= $this->buy_quantity) {
                            $freeSets      = intdiv($item->quantity, $this->buy_quantity);
                            $freeQty       = $freeSets * $this->get_quantity;

                            // Determine which product is free
                            if ($this->get_product_id) {
                                // Free product is a different product — we add it as a free item
                                $freeItems[] = [
                                    'product_id' => $this->get_product_id,
                                    'quantity'   => $freeQty,
                                ];
                            } else {
                                // Free units of the same product → discount = freeQty * unit_price
                                $unitPrice = $item->total_line_price / $item->quantity;
                                $discount += $freeQty * $unitPrice;
                            }
                        }
                    }
                }
                return ['discount' => round($discount, 2), 'free_items' => $freeItems];

            // ── 5. Spend X get Y amount off ───────────────────────────────
            case 'spend_x_get_y':
                if ($subtotal >= $this->min_spend) {
                    return ['discount' => round((float) $this->discount_amount, 2), 'free_items' => []];
                }
                return ['discount' => 0, 'free_items' => []];

            default:
                return ['discount' => 0, 'free_items' => []];
        }
    }

    /**
     * Human-readable label for display.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'percentage'    => 'خصم نسبة مئوية',
            'fixed'         => 'خصم ثابت',
            'bundle'        => 'باقة منتجات',
            'buy_x_get_y'   => 'اشترِ X واحصل على Y مجاناً',
            'spend_x_get_y' => 'اشترِ بـ X واحصل على خصم Y',
            default         => $this->type,
        };
    }
}
