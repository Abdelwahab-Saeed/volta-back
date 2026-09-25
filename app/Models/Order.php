<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

class Order extends Model
{
    use HasFactory;

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
    ];

    // Pre-piasters backup columns, removed by the drop_legacy_money_columns migration.
    protected $hidden = ['subtotal_legacy', 'shipping_cost_legacy', 'discount_amount_legacy', 'total_amount_legacy'];

    protected $casts = [
        'subtotal' => MoneyCast::class,
        'shipping_cost' => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
        'total_amount' => MoneyCast::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


}
