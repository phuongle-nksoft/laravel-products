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
                'title' => 'Brands',
                'link' => 'brands',
                'icon' => 'nav-icon fas fa-book-reader',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 1,
            ],
            [
                'title' => 'Regions',
                'link' => 'regions',
                'icon' => 'nav-icon fas fa-map-marked',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 2,
            ],
            [
                'title' => 'Vintages',
                'link' => 'vintages',
                'icon' => 'nav-icon fas fa-truck-moving',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 3,
            ],
            [
                'title' => 'Professionals',
                'link' => 'professionals',
                'icon' => 'nav-icon fas fa-user-shield',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 3,
            ],
            [
                'title' => 'Categories',
                'link' => 'categories',
                'icon' => 'nav-icon far fa-folder',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 4,
            ],
            [
                'title' => 'Tags',
                'link' => 'tags',
                'icon' => 'nav-icon fas fa-tags',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 5,
            ],
            [
                'title' => 'Products',
                'link' => 'products',
                'icon' => 'nav-icon fas fa-store-alt',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 6,
            ],
        ];
        $sales = [
            [
                'title' => 'Promotions',
                'link' => 'promotions',
                'icon' => 'nav-icon fas fa-percentage',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 2,
            ],
            [
                'title' => 'Customers',
                'link' => 'customers',
                'icon' => 'nav-icon fas fa-users',
                'is_active' => true,
                'roles_id' => json_encode([1, 2, 3]),
                'order_by' => 1,
            ],
            [
                'title' => 'Orders',
                'link' => 'orders',
                'icon' => 'nav-icon fas fa-luggage-cart',
                'is_active' => true,
                'roles_id' => json_encode([1, 2, 3]),
                'order_by' => 3,
            ],
            [
                'title' => 'Payments',
                'link' => 'payment',
                'icon' => 'nav-icon fas fa-file-invoice',
                'is_active' => true,
                'roles_id' => json_encode([1, 2, 3]),
                'order_by' => 2,
            ],
        ];
        $items = [
            [
                'title' => 'Products',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 1,
                'roles_id' => json_encode([1, 2]),
                'child' => serialize($products),
            ],
            [
                'title' => 'Sales',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 2,
                'roles_id' => json_encode([1, 2, 3]),
                'child' => serialize($sales),
            ],
        ];
        Navigations::saveItem($items);
    }
}
