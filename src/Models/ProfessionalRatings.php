<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;
class ProfessionalRatings extends NksoftModel
{
    protected $table = 'professional_ratings';
    protected $fillable = ['id', 'professionals_id', 'products_id', 'description', 'ratings'];
}
