<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Regions extends NksoftModel
{
    protected $table = 'regions';
    protected $fillable = ['id', 'name', 'parent_id', 'is_active', 'order_by', 'slug', 'description', 'meta_description'];
}
