<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enquiry type → canonical pricing / lead interest key
    |--------------------------------------------------------------------------
    |
    | When the public site sends a legacy `type` value, map it to the key used
    | in admin enquiry pricing (`enquiry_prices` JSON) and stored on leads
    | (`interests`). Omit entries for types that already match their key.
    |
    */
    'enquiry_type_aliases' => [
        'outdoor_kitchen' => 'outdoor_product',
    ],
];
