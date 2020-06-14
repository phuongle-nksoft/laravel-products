<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Notifications extends NksoftModel
{
    const FIELDS = ['id', 'name', 'description', 'customers_id'];
    protected $table = 'notifications';
    protected $fillable = self::FIELDS;

    public static function createItem($type = 1, $customerId)
    {
        $name = 'Đơn hàng mua';
        $description = 'Đơn hàng của bạn được đặt vào ngày';
        switch ($type) {
            case 3:
                $name = 'Đơn đang được vận chuyển';
                $description = 'Đơn hàng của bạn đang được vận chuyển';
                break;
            case 4:
                $name = 'Bạn đã nhận hàng';
                $description = 'Đơn hàng của bạn đã được nhận vào ngày';
                break;
            case 5:
                $name = 'Hủy đơn hàng';
                $description = 'Đơn hàng của bạn đã được hủy vì lý do không nhận hàng vào ngày';
                break;
        }
        $save = new self;
        $save->name = $name;
        $save->description = $description;
        $save->customers_id = $customerId;
        $save->save();
    }
}
