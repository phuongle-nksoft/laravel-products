<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Orders extends NksoftModel
{
    const FIELDS = ['id', 'customers_id', 'shippings_id', 'promotion_id', 'discount_code', 'discount_amount', 'total', 'area', 'price_contact', 'order_id', 'status', 'delivery_charges', 'note'];
    protected $table = 'orders';
    protected $fillable = self::FIELDS;
    public function payment()
    {
        return $this->belongsTo(Payments::class, 'orders_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetails::class, 'orders_id')->select(['id', 'orders_id', 'products_id', 'qty', 'price', 'special_price', 'discount', 'subtotal', 'name', 'created_at'])->with(['products']);
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shippings_id')->with(['customer', 'provinces', 'districts', 'wards']);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id')->with(['shipping']);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotions_id');
    }
}
