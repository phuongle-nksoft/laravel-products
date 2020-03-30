<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Products extends NksoftModel
{
    protected $table = 'products';
    protected $fillable = ['id', 'name', 'categories_id', 'vintages_id', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'professionals_rating', 'alcohol_content', 'volume', 'slug', 'description', 'meta_description'];

    public function categoryProductIndies()
    {
        return $this->hasMany('\Nksoft\Products\Models\CategoryProductsIndex', 'products_id');
    }

    public function vintages()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Vintages');
    }

    public function brands()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Brands');
    }

    public function regions()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Regions');
    }
}
