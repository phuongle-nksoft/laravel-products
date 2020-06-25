<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ChildProducts extends NksoftModel
{
    const FIELDS = ['id', 'parent_id', 'child_id', 'is_active'];
    protected $table = 'child_products';
    protected $fillable = self::FIELDS;

    public function parentProduct()
    {
        return $this->belongsTo(Products::class, 'parent_id')->where(['is_active' => 1])->with(['professionalsRating', 'images', 'firstCategory']);
    }

    public function childProducts()
    {
        return $this->belongsTo(Products::class, 'child_id')->where(['is_active' => 1])->with(['professionalsRating', 'images', 'firstCategory']);
    }
}
