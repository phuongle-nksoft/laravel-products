<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class TypeProducts extends NksoftModel
{
    const FIELDS = ['id', 'name', 'is_active', 'filter'];
    protected $table = 'type_products';
    protected $fillable = self::FIELDS;
}
