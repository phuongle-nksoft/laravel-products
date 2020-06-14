<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Brands extends NksoftModel
{
    const FIELDS = ['id', 'name', 'is_active', 'order_by', 'slug', 'video_id', 'description', 'type', 'meta_title', 'meta_description'];
    protected $table = 'brands';
    protected $fillable = self::FIELDS;

    public function products()
    {
        return $this->hasMany('\Nksoft\Products\Models\Products', 'brands_id')->where(['is_active' => 1])
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
    }
}
