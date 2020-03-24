<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
class Customers extends NksoftModel
{
    protected $table = 'customers';
    protected $fillable = ['id', 'name', 'is_active', 'password', 'email'];
    protected $hidden = ['password'];
}
