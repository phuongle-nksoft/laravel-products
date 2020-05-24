<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProductTags extends NksoftModel
{
    const FIELDS = ['id', 'tags_id', 'products_id', 'is_active'];
    protected $table = 'product_tags';
    protected $fillable = self::FIELDS;

    public function products()
    {
        return $this->belongsTo(Products::class, 'products_id')->where(['is_active' => 1])
            ->select(Products::FIELDS)
            ->with(['professionalsRating', 'images', 'firstCategory'])->orderBy('updated_at', 'desc');
    }

    public function tags()
    {
        return $this->belongsTo(Tags::class, 'tags_id');
    }
}
