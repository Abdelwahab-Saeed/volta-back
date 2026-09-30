{{-- Amount in piasters shown as pounds: <x-admin.money :value="$order->total_amount" /> --}}
@props(['value'])
<span {{ $attributes->merge(['class' => 'money']) }}>{{ \App\Support\Money::format($value) }} <small>ج.م</small></span>
