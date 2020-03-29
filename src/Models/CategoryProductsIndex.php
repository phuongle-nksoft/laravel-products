<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class CategoryProductsIndex extends NksoftModel
{
    protected $table = 'category_products_indices';
    protected $fillable = ['id', 'categories_id', 'products_id', 'is_active'];
    public function categories()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Categories', 'categories_id');
    }
    public function products()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Products', 'products_id');
    }
}
