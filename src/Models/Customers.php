<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
use Nksoft\Products\Models\Shipping;

class Customers extends NksoftModel
{
    const FIELDS = ['id', 'name', 'is_active', 'password', 'email', 'phone'];
    protected $table = 'customers';
    protected $fillable = self::FIELDS;
    protected $hidden = ['password'];

    public function shipping()
    {
        return $this->hasMany(Shipping::class, 'customers_id')->select(['id', 'customers_id', 'address', 'phone', 'name', 'company', 'is_default', 'note'])->orderBy('id', 'desc');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'customers_id')->with(['orderDetails'])->select(['id', 'customers_id', 'shippings_id', 'promotion_id', 'discount_code', 'discount_amount', 'total', 'status', 'created_at']);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlists::class, 'customers_id')->with('products');
    }
}
