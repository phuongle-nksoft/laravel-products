<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProfessionalRatings extends NksoftModel
{
    protected $table = 'professional_ratings';
    protected $fillable = ['id', 'professionals_id', 'products_id', 'description', 'ratings', 'show'];
    public function professional()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Professionals', 'professionals_id')->select(['id', 'name', 'description', 'short_name']);
    }
}
