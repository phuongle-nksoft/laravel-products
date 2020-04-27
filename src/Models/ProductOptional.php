<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProductOptional extends NksoftModel
{
    const FIELDS = ['id', 'name', 'products_id', 'is_active', 'order_by', 'slug', 'description', 'video_id', 'meta_description'];
    protected $table = 'product_optionals';
    protected $fillable = self::FIELDS;

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->with(['images']);
    }
}
