<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Provinces extends NksoftModel
{
    const FIELDS = ['id', 'name'];
    protected $table = 'provinces';
    protected $fillable = self::FIELDS;
}
