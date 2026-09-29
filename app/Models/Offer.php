<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Support\Money;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * An offer is a package the customer buys directly from the offer page (never through the cart):
 *   - bundle:      fixed price for one "set" = each linked product × its pivot quantity
 *   - buy_x_get_y: buy X units of a linked product, get Y more at get_discount_percent off (100 = free),
 *                  or get Y units of a different gift product (get_product_id), always free
 * Cart-wide discounts are coupons.
 */
class Offer extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    public const TYPES = ['bundle', 'buy_x_get_y'];

    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image',
        'type',
        'buy_quantity',
        'get_quantity',
        'get_discount_percent',
        'get_product_id',
        'bundle_price',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $hidden = ['legacy_bundle_offer_id'];

    // Money is integer piasters (see MoneyCast).
    protected $casts = [
        'bundle_price'         => MoneyCast::class,
        'buy_quantity'         => 'integer',
        'get_quantity'         => 'integer',
        'get_discount_percent' => 'integer',
        'is_active'            => 'boolean',
        'starts_at'            => 'datetime',
        'expires_at'           => 'datetime',
    ];

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
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
     * Convert admin form input (pounds) to stored units.
     */
    public static function fromInput(array $data): array
    {
        return Money::fromPoundsFields($data, ['bundle_price']);
    }

    /**
     * Calculate the discount this offer gives on the given lines.
     * All amounts are integer piasters.
     *
     * @param  Collection  $items     Each item must have: product_id, quantity, total_line_price (piasters)
     * @param  int         $subtotal  Lines subtotal in piasters
     * @return array{discount: int, free_items: array<int, array{product_id: int, quantity: int}>}
     */
    public function calculateDiscount(Collection $items, int $subtotal): array
    {
        $result = match ($this->type) {
            'bundle'      => ['discount' => $this->bundleDiscount($items), 'free_items' => []],
            'buy_x_get_y' => $this->buyXGetY($this->qualifyingLines($items)),
            default       => ['discount' => 0, 'free_items' => []],
        };

        // An offer can never discount more than the lines are worth.
        $result['discount'] = min($result['discount'], $subtotal);

        return $result;
    }

    /**
     * What the offer looked like when it was bought (piasters), stored on the order.
     */
    public function snapshot(int $sets, ?int $productId): array
    {
        return [
            'id'                   => $this->id,
            'name_ar'              => $this->name_ar,
            'name_en'              => $this->name_en,
            'type'                 => $this->type,
            'bundle_price'         => $this->bundle_price,
            'buy_quantity'         => $this->buy_quantity,
            'get_quantity'         => $this->get_quantity,
            'get_discount_percent' => $this->get_discount_percent,
            'get_product_id'       => $this->get_product_id,
            'products'             => $this->products->map(fn (Product $p) => [
                'id' => $p->id, 'name_ar' => $p->name_ar, 'quantity' => (int) $p->pivot->quantity,
            ])->values()->all(),
            'sets'                 => $sets,
            'product_id'           => $productId,
        ];
    }

    /**
     * Lines grouped per product.
     *
     * @return Collection<int, array{quantity: int, total: int}> keyed by product_id
     */
    private function lines(Collection $items): Collection
    {
        return $items->groupBy('product_id')->map(fn (Collection $group) => [
            'quantity' => (int) $group->sum('quantity'),
            'total'    => (int) $group->sum('total_line_price'),
        ]);
    }

    private function qualifyingLines(Collection $items): Collection
    {
        return $this->lines($items)->only($this->products->pluck('id')->all());
    }

    // ── Bundle: fixed price per complete set (each product × its quantity) ──
    private function bundleDiscount(Collection $items): int
    {
        $bundle = $this->products->mapWithKeys(fn (Product $p) => [$p->id => max(1, (int) $p->pivot->quantity)]);
        if ($bundle->isEmpty() || $this->bundle_price === null) {
            return 0;
        }

        $lines = $this->lines($items);
        if ($bundle->keys()->contains(fn ($id) => !$lines->has($id))) {
            return 0; // not every bundle product is there
        }

        $sets = $bundle->map(fn ($perSet, $id) => intdiv($lines[$id]['quantity'], $perSet))->min();
        if ($sets < 1) {
            return 0;
        }

        // What those sets cost without the offer (units beyond full sets stay at normal price).
        $regular = $bundle->map(fn ($perSet, $id) => (int) round($lines[$id]['total'] * $sets * $perSet / $lines[$id]['quantity']))->sum();

        return max(0, $regular - $sets * $this->bundle_price);
    }

    // ── Buy X get Y (at a percentage off, or a different free gift product) ──
    private function buyXGetY(Collection $lines): array
    {
        $buy = (int) $this->buy_quantity;
        $get = (int) $this->get_quantity;
        if ($buy < 1 || $get < 1) {
            return ['discount' => 0, 'free_items' => []];
        }

        // Gift is a different product: every X units bought earn Y gift units, added to the order as a free line.
        if ($this->get_product_id) {
            $freeQty = intdiv($lines->sum('quantity'), $buy) * $get;

            return [
                'discount'   => 0,
                'free_items' => $freeQty > 0 ? [['product_id' => (int) $this->get_product_id, 'quantity' => $freeQty]] : [],
            ];
        }

        // Same product: each group is X paid + Y discounted units, e.g. buy 2 get 1 → every 3 units, 1 is discounted.
        $percent = $this->get_discount_percent ?? 100;
        $discount = $lines->sum(function (array $line) use ($buy, $get, $percent) {
            $discountedUnits = intdiv($line['quantity'], $buy + $get) * $get;
            $unitsValue = (int) round($line['total'] * $discountedUnits / $line['quantity']);

            return (int) round($unitsValue * $percent / 100);
        });

        return ['discount' => $discount, 'free_items' => []];
    }

    /**
     * What the offer gives, in the current locale, built from the offer's own numbers
     * (so the text can never disagree with what checkout charges). Needs products (and freeProduct for gifts) loaded.
     */
    public function summary(): string
    {
        if ($this->type === 'bundle') {
            $items = $this->products
                ->map(fn (Product $p) => __('offers.item', ['quantity' => (int) $p->pivot->quantity, 'name' => $p->name]))
                ->implode(__('offers.separator'));

            return __('offers.summary.bundle', ['items' => $items, 'price' => Money::formatCompact((int) $this->bundle_price)]);
        }

        if ($this->type === 'buy_x_get_y') {
            $numbers = ['buy' => $this->buy_quantity, 'get' => $this->get_quantity];

            if ($this->get_product_id) {
                return __('offers.summary.buy_x_get_gift', $numbers + ['gift' => $this->freeProduct?->name ?? '']);
            }

            return (int) $this->get_discount_percent === 100
                ? __('offers.summary.buy_x_get_y_free', $numbers)
                : __('offers.summary.buy_x_get_y', $numbers + ['percent' => $this->get_discount_percent]);
        }

        return '';
    }

    /**
     * Type name in the current locale (API), or in a given one (the admin dashboard is Arabic-only).
     */
    public static function typeLabel(?string $type, ?string $locale = null): string
    {
        $key = "offers.types.{$type}";
        $label = __($key, [], $locale);

        return $label === $key ? (string) $type : $label;
    }
}
