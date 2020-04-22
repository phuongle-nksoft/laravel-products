<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Professionals extends NksoftModel
{
    const FIELDS = ['id', 'name', 'short_name', 'description'];
    protected $table = 'professionals';
    protected $fillable = self::FIELDS;
}
