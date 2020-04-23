<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Wards extends NksoftModel
{
    const FIELDS = ['id', 'name', 'districts_id'];
    protected $table = 'wards';
    protected $fillable = self::FIELDS;
}
