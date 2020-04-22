<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Orders extends NksoftModel
{
    const FIELDS = ['id', 'customers_id', 'shippings_id', 'promotion_id', 'discount_code', 'discount_amount', 'total', 'status'];
    protected $table = 'orders';
    protected $fillable = self::FIELDS;
    public function payment()
    {
        return $this->belongsTo(Payments::class, 'orders_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'orders_id')->select(['id', 'orders_id', 'products_id', 'qty', 'price', 'special_price', 'subtotal', 'name', 'created_at'])->with(['products']);
    }
}
