{{-- Order status badge. Labels and colours for every OrderStatus live here only. --}}
@props(['status'])
@php
    $map = [
        'pending'    => ['قيد الانتظار', 'badge-warning'],
        'processing' => ['قيد التجهيز', 'badge-info'],
        'shipped'    => ['تم الشحن', 'badge-violet'],
        'delivered'  => ['تم التوصيل', 'badge-success'],
        'cancelled'  => ['ملغي', 'badge-danger'],
    ];
    [$label, $class] = $map[$status] ?? [$status, 'badge-neutral'];
@endphp
<span {{ $attributes->merge(['class' => "$class badge-dot"]) }}>{{ $label }}</span>
