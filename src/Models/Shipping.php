<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Shipping extends NksoftModel
{
    protected $table = 'shippings';
    protected $fillable = ['id', 'customers_id', 'address', 'phone', 'name', 'note'];
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id');
    }
}
