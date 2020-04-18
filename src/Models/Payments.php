<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Payments extends NksoftModel
{
    protected $table = 'payments';
    protected $fillable = ['id', 'Amount', 'BankCode', 'BankTranNo', 'CardType', 'OrderInfo', 'PayDate', 'ResponseCode', 'TmnCode', 'TransactionNo', 'TxnRef', 'orders_id', 'SecureHashType', 'SecureHash'];
}
