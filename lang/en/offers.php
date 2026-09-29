<?php

// Offer texts the API sends ready to display (OfferResource), so the frontend never hardcodes offer wording.
return [
    'types' => [
        'bundle'      => 'Bundle deal',
        'buy_x_get_y' => 'Buy & get',
    ],

    'summary' => [
        'bundle'           => ':items for EGP :price',
        'buy_x_get_y'      => 'Buy :buy, get :get at :percent% off',
        'buy_x_get_y_free' => 'Buy :buy, get :get free',
        'buy_x_get_gift'   => 'Buy :buy, get :get × :gift free',
    ],

    // Why an offer cannot be bought right now (OfferPricing issues), shown to the customer
    'issues' => [
        'product_unavailable' => ':name is not available right now',
        'gift_unavailable'    => 'The gift product of this offer is not available right now',
        'no_saving'           => 'This offer is not available right now',
        'out_of_stock'        => 'The requested quantity of :name is not in stock',
    ],
    'unavailable'    => 'This offer is not available or has expired',
    'price_changed'  => 'The offer price has changed. Please review the new total before placing the order',
    'sets_range'     => 'The number of times must be between 1 and :max.',
    'choose_product' => "Choose one of this offer's products.",

    // "3 × Charger + 1 × Earbuds"
    'item'      => ':quantity × :name',
    'separator' => ' + ',
];
