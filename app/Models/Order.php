<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'phone_number_backup',
        'city',
        'state',
        'shipping_way',
        'address_line',
        'status',
        'payment_method',
        'notes',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'coupon_code',
        'offer_id',
        'offer_discount',
        'offer_snapshot',
        'idempotency_key',
    ];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['subtotal_legacy', 'shipping_cost_legacy', 'discount_amount_legacy', 'total_amount_legacy',
        // Internal: the snapshot holds piasters, and the key is the client's retry token. OrderResource exposes what the frontend needs.
        'offer_snapshot', 'idempotency_key'];

    protected $casts = [
        'subtotal'        => MoneyCast::class,
        'shipping_cost'   => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
        'total_amount'    => MoneyCast::class,
        'offer_discount'  => MoneyCast::class,
        'offer_snapshot'  => 'array',
    ];

    // An order keeps pointing at its customer and offer after they are deleted (same as OrderItem::product()).
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class)->withTrashed();
    }


}
