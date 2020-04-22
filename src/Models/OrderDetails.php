<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class OrderDetails extends NksoftModel
{
    const FIELDS = ['id', 'orders_id', 'products_id', 'qty', 'price', 'special_price', 'subtotal', 'name'];
    protected $table = 'order_details';
    protected $fillable = self::FIELDS;
    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->with(['images', 'categoryProductIndies', 'firstCategory', 'vintages', 'brands', 'regions', 'professionalsRating', 'orderDetails']);
    }
}
