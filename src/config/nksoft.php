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
    'filterCustom' => [
        ['id' => 1, 'text' => 'Rươu điểm cao', 'type' => 'professional', 'slug' => 'ruou-diem-cao', 'key' => 'ratings', 'value' => 90, 'condition' => 'gt'],
        ['id' => 2, 'text' => 'Rượu 100 điểm', 'type' => 'professional', 'slug' => 'ruou-100-diem', 'key' => 'ratings', 'value' => 100],
        ['id' => 3, 'text' => 'Rượu từ 90 điểm', 'type' => 'professional', 'slug' => 'ruou-tu-90-diem', 'key' => 'ratings', 'value' => 90, 'condition' => 'gt'],
        ['id' => 4, 'text' => 'Rượu cỡ lớn', 'type' => 'products', 'slug' => 'ruou-co-lon', 'key' => 'volume', 'value' => 1500, 'condition' => 'gt'],
        ['id' => 5, 'text' => 'Chai 1.5L', 'type' => 'products', 'slug' => 'chai-1-5-l', 'key' => 'volume', 'value' => 1500],
        ['id' => 6, 'text' => 'Chai 3L', 'type' => 'products', 'slug' => 'chai-3-l', 'key' => 'volume', 'value' => 3000],
        ['id' => 7, 'text' => 'Chai lớn đặt biệt', 'type' => 'products', 'slug' => 'chai-lon-dat-biet', 'key' => 'volume', 'value' => 3000, 'condition' => 'gt'],
        ['id' => 8, 'text' => 'Rượu hiếm có', 'type' => 'products', 'slug' => 'ruou-hiem-co', 'key' => 'scarce', 'value' => 1],
    ],
];
