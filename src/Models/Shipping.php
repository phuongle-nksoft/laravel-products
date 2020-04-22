<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Shipping extends NksoftModel
{
    const FIELDS = ['id', 'customers_id', 'address', 'phone', 'name', 'company', 'is_default', 'last_shipping', 'note'];
    protected $table = 'shippings';
    protected $fillable = self::FIELDS;
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id');
    }
}
