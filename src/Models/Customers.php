<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
use Nksoft\Products\Models\Shipping;

class Customers extends NksoftModel
{
    protected $table = 'customers';
    protected $fillable = ['id', 'name', 'is_active', 'password', 'email', 'phone'];
    protected $hidden = ['password'];

    public function shipping()
    {
        return $this->hasMany(Shipping::class, 'customers_id')->select(['id', 'customers_id', 'address', 'phone', 'name', 'company', 'is_default', 'note'])->orderBy('id', 'desc');
    }
}
