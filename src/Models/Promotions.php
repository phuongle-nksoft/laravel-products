<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Promotions extends NksoftModel
{
    protected $table = 'promotions';
    protected $fillable = ['id', 'name', 'code', 'coupon_type', 'discount_amount', 'expice_date', 'start_date', 'is_active', 'discount_qty', 'product_ids', 'description', 'simple_action'];
}
