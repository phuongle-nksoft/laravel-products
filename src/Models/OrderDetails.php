<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class OrderDetails extends NksoftModel
{
    protected $table = 'order_details';
    protected $fillable = ['id', 'orders_id', 'products_id', 'qty', 'price', 'special_price', 'subtotal', 'name'];
}