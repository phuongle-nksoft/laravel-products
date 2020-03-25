<?php

namespace Nksoft\Products\Models;
use Nksoft\Master\Models\NksoftModel;
class Brands extends NksoftModel
{
    protected $table = 'brands';
    protected $fillable = ['id', 'name', 'is_active', 'order_by', 'slug', 'description', 'meta_description'];
}
