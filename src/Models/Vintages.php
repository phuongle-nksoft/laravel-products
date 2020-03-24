<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Vintages extends NksoftModel
{
    protected $table = 'vintages';
    protected $fillable = ['id', 'name', 'parent_id', 'is_active', 'order_by', 'slug', 'description', 'meta_description'];
}
