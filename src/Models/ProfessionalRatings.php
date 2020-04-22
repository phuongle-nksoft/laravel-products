<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProfessionalRatings extends NksoftModel
{
    const FIELDS = ['id', 'professionals_id', 'products_id', 'description', 'ratings', 'show'];
    protected $table = 'professional_ratings';
    protected $fillable = self::FIELDS;
    public function professional()
    {
        return $this->belongsTo(Professionals::class, 'professionals_id')->select(Professionals::FIELDS);
    }
}
