<?php

namespace Nksoft\Products\database\seeds;

use Illuminate\Database\Seeder;
use Nksoft\Master\Models\Navigations;

class NavigationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'title' => 'Categories',
                'link' => 'categories',
                'icon' => 'nav-icon far fa-folder',
                'is_active' => true,
                'order_by' => 1,
            ],
            [
                'title' => 'Products',
                'link' => 'products',
                'icon' => 'nav-icon fas fa-store-alt',
                'is_active' => true,
                'order_by' => 2,
            ]
        ];
        $sales = [
            [
                'title' => 'Customers',
                'link' => 'customers',
                'icon' => 'nav-icon fas fa-users',
                'is_active' => true,
                'order_by' => 1,
            ],
            [
                'title' => 'Shippings',
                'link' => 'shippings',
                'icon' => 'nav-icon fas fa-map-marker-alt',
                'is_active' => true,
                'order_by' => 2,
            ],
            [
                'title' => 'Orders',
                'link' => 'orders',
                'icon' => 'nav-icon fas fa-luggage-cart',
                'is_active' => true,
                'order_by' => 3,
            ],
            [
                'title' => 'Payments',
                'link' => 'payment',
                'icon' => 'nav-icon fas fa-file-invoice',
                'is_active' => true,
                'order_by' => 2,
            ]
        ];
        $items = [
            [
                'title' => 'Products',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 1,
                'child' => serialize($products),
            ],
            [
                'title' => 'Sales',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 2,
                'child' => serialize($sales),
            ],
        ];
        Navigations::saveItem($items);
    }
}
