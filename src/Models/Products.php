<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
class Products extends NksoftModel
{
    protected $table = 'products';
    protected $fillable = ['id', 'name', 'categories_id', 'vintages_id', 'regions_id', 'brands_id', 'is_active', 'order_by', 'price', 'description', 'slug', 'meta_description'];
}
