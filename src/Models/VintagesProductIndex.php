<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class VintagesProductIndex extends NksoftModel
{
    const FIELDS = ['id', 'vintages_id', 'products_id', 'is_active'];
    protected $table = 'vintages_product_indices';
    protected $fillable = self::FIELDS;
    public function vintages()
    {
        return $this->belongsTo(Vintages::class, 'vintages_id')->with(['images']);
    }

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(Products::FIELDS)
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
    }
}
