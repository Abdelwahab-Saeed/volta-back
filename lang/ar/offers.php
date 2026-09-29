<?php

// Offer texts the API sends ready to display (OfferResource), so the frontend never hardcodes offer wording.
return [
    'types' => [
        'bundle'      => 'باقة بسعر ثابت',
        'buy_x_get_y' => 'اشترِ واحصل على',
    ],

    'summary' => [
        'bundle'           => ':items بسعر :price ج.م',
        'buy_x_get_y'      => 'اشترِ :buy واحصل على :get بخصم :percent%',
        'buy_x_get_y_free' => 'اشترِ :buy واحصل على :get مجاناً',
        'buy_x_get_gift'   => 'اشترِ :buy واحصل على :get من :gift هدية',
    ],

    // Why an offer cannot be bought right now (OfferPricing issues), shown to the customer
    'issues' => [
        'product_unavailable' => 'المنتج :name غير متاح حالياً',
        'gift_unavailable'    => 'منتج الهدية الخاص بالعرض غير متاح حالياً',
        'no_saving'           => 'العرض غير متاح حالياً',
        'out_of_stock'        => 'الكمية المطلوبة من :name غير متوفرة حالياً',
    ],
    'unavailable'    => 'العرض غير متاح أو انتهت صلاحيته',
    'price_changed'  => 'تغيّر سعر العرض، راجع الإجمالي الجديد قبل إتمام الطلب',
    'sets_range'     => 'عدد مرات العرض يجب أن يكون بين 1 و :max.',
    'choose_product' => 'اختر منتجاً من منتجات هذا العرض.',

    // "3 × شاحن + 1 × سماعة"
    'item'      => ':quantity × :name',
    'separator' => ' + ',
];
