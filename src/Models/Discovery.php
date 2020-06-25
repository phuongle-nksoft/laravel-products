<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Discovery extends NksoftModel
{
    const FIELDS = ['id', 'name', 'is_active', 'order_by', 'slug', 'description', 'type', 'key', 'value', 'condition', 'meta_title', 'meta_description', 'canonical_link'];
    protected $table = 'discoveries';
    protected $fillable = self::FIELDS;
}
