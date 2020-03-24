<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Products extends NksoftModel
{
    protected $table = 'products';
    protected $fillable = ['id', 'name', 'categories_id', 'vintages_id', 'regions_id', 'brands_id', 'is_active', 'order_by', 'price', 'description', 'slug', 'meta_description'];

    public function category()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Categories');
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
