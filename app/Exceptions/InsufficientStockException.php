<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

/**
 * Thrown while placing an order when a product (or an offer gift) does not have enough stock.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $available,
        public readonly int $requested,
    ) {
        parent::__construct("Insufficient stock for product {$product->id}");
    }

    public function details(): array
    {
        return [
            'product' => $this->product->name,
            'available' => $this->available,
            'requested' => $this->requested,
        ];
    }
}
