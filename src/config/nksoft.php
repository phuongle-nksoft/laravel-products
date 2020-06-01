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
    'sortCustom' => [
        ['id' => 1, 'name' => 'Rươu điểm cao', 'type' => 'professional', 'slug' => 'ruou-diem-cao'],
        ['id' => 2, 'name' => 'Rượu 100 điểm', 'type' => 'professional', 'slug' => 'ruou-100-diem'],
        ['id' => 3, 'name' => 'Rượu từ 90 điểm', 'type' => 'professional', 'slug' => 'ruou-tu-90-diem'],
        ['id' => 4, 'name' => 'Rượu cỡ lớn', 'type' => 'products', 'slug' => 'ruou-co-lon'],
        ['id' => 5, 'name' => 'Chai 1.5L', 'type' => 'products', 'slug' => 'chai-1-5-l'],
        ['id' => 6, 'name' => 'Chai 3L', 'type' => 'products', 'slug' => 'chai-3-l'],
        ['id' => 7, 'name' => 'Chai lớn đặt biệt', 'type' => 'products', 'slug' => 'chai-lon-dat-biet'],
        ['id' => 8, 'name' => 'Rượu hiếm có', 'type' => 'products', 'slug' => 'ruou-hiem-co'],
    ],
];
