<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Products extends NksoftModel
{
    protected $table = 'products';
    protected $fillable = ['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description', 'views', 'price_contact'];

    public function categoryProductIndies()
    {
        return $this->hasMany(CategoryProductsIndex::class, 'products_id')->with(['categories']);
    }

    public function firstCategory()
    {
        return $this->hasOne(CategoryProductsIndex::class, 'categories_id')->with(['categories']);
    }

    public function vintages()
    {
        return $this->hasMany(VintagesProductIndex::class, 'products_id')->with(['vintages']);
    }

    public function brands()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Brands')->with(['images'])->select(['id', 'name', 'is_active', 'order_by', 'slug', 'video_id', 'description'])->orderBy('order_by', 'asc')->orderBy('created_at', 'desc');
    }

    public function regions()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Regions')->with(['parent', 'images'])->select(['id', 'name', 'parent_id', 'is_active', 'order_by', 'slug', 'description', 'video_id'])->orderBy('order_by', 'asc')->orderBy('created_at', 'desc');
    }

    public function professionalsRating()
    {
        return $this->hasMany('\Nksoft\Products\Models\ProfessionalRatings', 'products_id')->with(['professional'])->select(['id', 'professionals_id', 'products_id', 'description', 'ratings', 'show'])->orderBy('created_at', 'desc');
    }

    public function orderDetails()
    {
        return $this->belongsTo('\Nksoft\Products\Models\OrderDetails', 'products_id')->orderBy('created_at', 'desc');
    }

    public function productTags()
    {
        return $this->hasMany(ProductTags::class, 'products_id');
    }
}
