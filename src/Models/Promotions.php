<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Promotions extends NksoftModel
{
    const FIELDS = ['id', 'name', 'code', 'coupon_type', 'discount_amount', 'expice_date', 'start_date', 'is_active', 'discount_qty', 'product_ids', 'description', 'simple_action', 'all_products'];
    protected $table = 'promotions';
    protected $fillable = self::FIELDS;
    /**
     * Get list category to product
     */
    public static function GetListByProduct(array $idSelected)
    {
        $data = [];
        $fs = Products::orderBy('order_by')->get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => in_array($item->id, $idSelected) ? true : false,
                );
                $data[] = array(
                    'name' => $item->name . ' ' . $item->year_of_manufacture,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => null,
                    'slug' => $item->slug,
                );
            }
        }
        return $data;
    }
}
