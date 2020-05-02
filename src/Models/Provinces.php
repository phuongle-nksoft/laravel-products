<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Provinces extends NksoftModel
{
    const FIELDS = ['id', 'name', 'area'];
    protected $table = 'provinces';
    protected $fillable = self::FIELDS;

    public function districts()
    {
        return $this->hasMany(Districts::class, 'provinces_id')->orderBy('name', 'asc')->select(Districts::FIELDS)->with(['wards']);
    }
}
