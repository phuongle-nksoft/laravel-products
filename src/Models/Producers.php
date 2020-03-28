<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Producers extends NksoftModel
{
    protected $table = 'producers';
    protected $fillable = ['id', 'name', 'description'];
}
