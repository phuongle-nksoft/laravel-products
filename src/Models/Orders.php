<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Orders extends NksoftModel
{
    public function payment()
    {
        return $this->belongsTo(Payments::class, 'orders_id');
    }
}
