<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
class Ratings extends NksoftModel
{
    protected $table = 'ratings';
    protected $fillable = ['id', 'products_id', 'description', 'ratings'];
}
