<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Ratings extends NksoftModel
{
    const FIELDS = ['id', 'products_id', 'description', 'ratings'];
    protected $table = 'ratings';
    protected $fillable = self::FIELDS;
}
