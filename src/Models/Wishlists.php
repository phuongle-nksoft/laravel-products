<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Wishlists extends NksoftModel
{
    protected $table = 'wishlists';
    protected $fillable = ['id', 'customers_id', 'products_id'];

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(['id', 'name', 'vintages_id', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description', 'views'])
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating', 'orderDetails']);
    }
}
