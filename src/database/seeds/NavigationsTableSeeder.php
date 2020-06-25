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
                'title' => 'Discovery',
                'link' => 'discoveries',
                'icon' => 'nav-icon fa fa-cc-discover',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 1,
            ],
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
                'roles_id' => json_encode([1, 3]),
                'order_by' => 1,
            ],
            [
                'title' => 'Orders',
                'link' => 'orders',
                'icon' => 'nav-icon fas fa-luggage-cart',
                'is_active' => true,
                'roles_id' => json_encode([1, 3]),
                'order_by' => 3,
            ],
        ];
        $promotion = [
            [
                'title' => 'banners',
                'link' => 'banners',
                'icon' => 'nav-icon far fa-images',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 2,
            ],
            [
                'title' => 'Promotion Images',
                'link' => 'promotion-images',
                'icon' => 'nav-icon fas fa-link',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 1,
            ],
        ];
        $clientRequest = [
            [
                'title' => 'Contacts',
                'link' => 'contacts',
                'icon' => 'nav-icon fas fa-address-card',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 1,
            ],
            [
                'title' => 'Recruits',
                'link' => 'recruits',
                'icon' => 'nav-icon fas fa-receipt',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 2,
            ],
            [
                'title' => 'Comments',
                'link' => 'comments',
                'icon' => 'nav-icon fas fa-comment-dots',
                'is_active' => true,
                'roles_id' => json_encode([1, 2]),
                'order_by' => 3,
            ],
        ];
        $items = [
            [
                'title' => 'Promotions',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 1,
                'roles_id' => json_encode([1, 2]),
                'child' => serialize($promotion),
            ],
            [
                'title' => 'Products',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 2,
                'roles_id' => json_encode([1, 2]),
                'child' => serialize($products),
            ],
            [
                'title' => 'Sales',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 3,
                'roles_id' => json_encode([1, 2, 3]),
                'child' => serialize($sales),
            ],
            [
                'title' => 'Client Request',
                'link' => '#',
                'icon' => '',
                'is_active' => true,
                'order_by' => 1,
                'roles_id' => json_encode([1, 2]),
                'child' => serialize($clientRequest),
            ],
        ];
        Navigations::saveItem($items);
    }
}
