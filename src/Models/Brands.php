<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Brands extends NksoftModel
{
    protected $table = 'brands';
    protected $fillable = ['id', 'name', 'is_active', 'order_by', 'slug', 'video_id', 'description', 'meta_description'];

    public function products()
    {
        return $this->hasMany('\Nksoft\Products\Models\Products', 'brands_id')->where(['is_active' => 1])
            ->select(['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description'])
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
    }
}
