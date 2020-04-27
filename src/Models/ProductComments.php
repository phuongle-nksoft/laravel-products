<?php
namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class ProductComments extends NksoftModel
{
    const FIELDS = ['id', 'products_id', 'customers_id', 'description', 'parent_id', 'status', 'name'];
    protected $table = 'product_comments';
    protected $fillable = self::FIELDS;

    public function children()
    {
        return $this->belongsTo('\Nksoft\Products\Models\ProductComments', 'parent_id');
    }
}
