<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComments extends Model
{
    //
}

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProductComments extends NksoftModel
{
    protected $table = 'product_comments';
    protected $fillable = ['id', 'products_id', 'customers_id', 'description', 'parent_id', 'status', 'name'];

    public function children()
    {
        return $this->belongsTo('\Nksoft\Products\Models\ProductComments', 'parent_id');
    }
}
