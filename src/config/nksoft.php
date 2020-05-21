<?php
return [
    'productTemplates' => [
        ['id' => 1, 'name' => 'Default'],
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
        ['id' => 6, 'name' => trans('nksoft::common.Price Contact')],
    ],
    'productType' => [
        ['id' => 1, 'name' => trans('nksoft::products.Wine')],
        ['id' => 2, 'name' => trans('nksoft::products.Glass')],
        ['id' => 3, 'name' => trans('nksoft::products.Beer')],
        ['id' => 4, 'name' => trans('nksoft::products.Other')],
    ],
];
