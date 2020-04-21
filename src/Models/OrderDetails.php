<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class OrderDetails extends NksoftModel
{
    protected $table = 'order_details';
    protected $fillable = ['id', 'orders_id', 'products_id', 'qty', 'price', 'special_price', 'subtotal', 'name'];
    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description', 'views'])
            ->with(['images', 'categoryProductIndies', 'firstCategory', 'vintages', 'brands', 'regions', 'professionalsRating', 'orderDetails']);
    }
}
