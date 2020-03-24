<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Professionals extends NksoftModel
{
    protected $table = 'professionals';
    protected $fillable = ['id', 'name'];
}
