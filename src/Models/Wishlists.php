<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Wishlists extends NksoftModel
{
    const FIELDS = ['id', 'customers_id', 'products_id'];
    protected $table = 'wishlists';
    protected $fillable = self::FIELDS;

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(Products::FIELDS)
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating', 'orderDetails']);
    }
}
