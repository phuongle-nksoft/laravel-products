<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class CategoryProductsIndex extends NksoftModel
{
    const FIELDS = ['id', 'categories_id', 'products_id', 'is_active'];
    protected $table = 'category_products_indices';
    protected $fillable = self::FIELDS;
    public function categories()
    {
        return $this->belongsTo(Categories::class, 'categories_id')->with(['images']);
    }

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
    }
}
