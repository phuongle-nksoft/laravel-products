<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Districts extends Model
{
    //
}

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Districts extends NksoftModel
{
    const FIELDS = ['id', 'name', 'province_id'];
    protected $table = 'districts';
    protected $fillable = self::FIELDS;
}
