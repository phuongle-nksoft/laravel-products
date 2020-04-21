<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProductTags extends NksoftModel
{
    protected $table = 'product_tags';
    protected $fillable = ['id', 'tags_id', 'products_id', 'is_active'];

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description'])
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
    }

    public function tags()
    {
        return $this->belongsTo(Tags::class, 'tags_id');
    }
}
