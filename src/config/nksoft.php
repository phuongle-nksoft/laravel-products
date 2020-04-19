<?php
return [
    'productTemplates' => [
        ['id' => 1, 'name' => 'Default'],
        ['id' => 2, 'name' => 'List Layout'],
        ['id' => 3, 'name' => 'Promotion Layout'],
    ],
    'addCart' => 'addCart',
    'customer' => 'customer',
    'simpleAction' => [
        ['id' => 1, 'name' => trans('nksoft::products.Percent Of Product price discount')],
        ['id' => 2, 'name' => trans('nksoft::products.Fixed Amount Discount')],
    ],
    'orderStatus' => [
        ['id' => 1, 'name' => trans('nksoft::products.Pendding')],
        ['id' => 2, 'name' => trans('nksoft::products.Success')],
        ['id' => 3, 'name' => trans('nksoft::products.Shipping')],
        ['id' => 4, 'name' => trans('nksoft::products.Done')],
        ['id' => 5, 'name' => trans('nksoft::products.Cancel')],
    ],
];
