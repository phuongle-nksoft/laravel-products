<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Shipping extends NksoftModel
{
    const FIELDS = ['id', 'customers_id', 'address', 'phone', 'name', 'company', 'is_default', 'last_shipping', 'note', 'provinces_id', 'districts_id', 'wards_id'];
    protected $table = 'shippings';
    protected $fillable = self::FIELDS;
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id');
    }

    public function provinces()
    {
        return $this->belongsTo(Provinces::class, 'provinces_id');
    }
    public function districts()
    {
        return $this->belongsTo(Districts::class, 'districts_id');
    }
    public function wards()
    {
        return $this->belongsTo(Wards::class, 'wards_id');
    }
}
