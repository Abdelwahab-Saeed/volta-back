<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Enums\PaymentMethod;
use App\Exceptions\InsufficientStockException;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * What the cart checkout and the offer checkout share: customer fields, the idempotency key,
 * the expected-total guard and the response shapes.
 */
trait PlacesOrders
{
    protected function customerRules(): array
    {
        return [
            'full_name'           => 'required|string|max:255',
            'phone_number'        => 'required|string|max:255',
            'phone_number_backup' => 'nullable|string|max:255',
            'city'                => 'required|string|max:255',
            'state'               => 'required|string|max:255',
            'address_line'        => 'nullable|string|max:255',
            'shipping_way'        => 'required|string|in:home,office,pickup',
            'payment_method'      => ['required', Rule::in(PaymentMethod::values())],
            'notes'               => 'nullable|string',
            // The total (in pounds) the customer was shown; a different server total means prices changed meanwhile.
            'expected_total'      => 'nullable|numeric|min:0',
            'idempotency_key'     => 'nullable|string|max:64',
        ];
    }

    protected function customerAttributes(Request $request, $user): array
    {
        return [
            'user_id'             => $user?->id,
            'full_name'           => $request->full_name,
            'phone_number'        => $request->phone_number,
            'phone_number_backup' => $request->phone_number_backup,
            'city'                => $request->city,
            'state'               => $request->state,
            'address_line'        => $request->address_line,
            'shipping_way'        => $request->shipping_way,
            'payment_method'      => $request->payment_method,
            'notes'               => $request->notes,
            'idempotency_key'     => $this->idempotencyKey($request),
        ];
    }

    /**
     * Sent by the client once per checkout attempt, as the Idempotency-Key header (or idempotency_key field).
     */
    protected function idempotencyKey(Request $request): ?string
    {
        $key = $request->header('Idempotency-Key') ?: $request->input('idempotency_key');

        return $key ? substr((string) $key, 0, 64) : null;
    }

    protected function totalChanged(Request $request, int $total): bool
    {
        return $request->filled('expected_total') && Money::fromPounds($request->expected_total) !== $total;
    }

    protected function orderCreatedResponse(Order $order, bool $replayed = false): JsonResponse
    {
        // A replayed key returns the order it created the first time, with 200 instead of 201.
        return $this->successResponse(
            new OrderResource($order->load('items.product')),
            'تم إتمام الطلب بنجاح',
            $replayed ? 200 : 201
        );
    }

    protected function insufficientStockResponse(InsufficientStockException $e, string $message): JsonResponse
    {
        return $this->errorResponse($message . $e->product->name, 422, $e->details());
    }
}
