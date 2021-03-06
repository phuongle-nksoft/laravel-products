<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Products extends NksoftModel
{
    const FIELDS = ['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'vintages_banner_id', 'unit', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'qty', 'rate', 'scarce', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description', 'views', 'type', 'meta_title', 'price_contact', 'canonical_link'];
    protected $table = 'products';
    protected $fillable = self::FIELDS;

    public function categoryProductIndies()
    {
        return $this->hasMany(CategoryProductsIndex::class, 'products_id')->with(['categories']);
    }

    public function firstCategory()
    {
        return $this->hasOne(CategoryProductsIndex::class, 'products_id')->with(['categories']);
    }

    public function vintages()
    {
        return $this->hasMany(VintagesProductIndex::class, 'products_id')->with(['vintages']);
    }

    public function vintageBanner()
    {
        return $this->belongsTo(Vintages::class, 'vintages_banner_id')->where('id', '<>', 35)->with(['images']);
    }

    public function brands()
    {
        return $this->belongsTo(Brands::class, 'brands_id')->with(['images'])->select(Brands::FIELDS)->orderBy('order_by', 'asc')->orderBy('created_at', 'desc');
    }

    public function regions()
    {
        return $this->belongsTo(Regions::class)->with(['parent', 'images'])->select(Regions::FIELDS)->orderBy('order_by', 'asc')->orderBy('created_at', 'desc');
    }

    public function professionalsRating()
    {
        return $this->hasMany(ProfessionalRatings::class, 'products_id')->with(['professional'])->select(ProfessionalRatings::FIELDS)->orderBy('created_at', 'desc');
    }

    public function orderDetails()
    {
        return $this->belongsTo(OrderDetails::class, 'products_id')->orderBy('created_at', 'desc');
    }

    public function productTags()
    {
        return $this->hasMany(ProductTags::class, 'products_id');
    }

    public function productOptional()
    {
        return $this->hasOne(ProductOptional::class, 'products_id')->with(['images']);
    }

    public function childProducts()
    {
        return $this->hasMany(ChildProducts::class, 'parent_id')->with(['childProducts']);
    }

    /**
     * Get list product
     */
    public static function GetListProducts(array $idSelected)
    {
        $data = [];
        $fs = self::orderBy('order_by')->get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => in_array($item->id, $idSelected) ? true : false,
                );
                $data[] = array(
                    'name' => $item->name . ' ' . $item->year_of_manufacture,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => null,
                    'slug' => $item->slug,
                );
            }
        }
        return $data;
    }
}
