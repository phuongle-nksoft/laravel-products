<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Tags extends NksoftModel
{
    const FIELDS = ['id', 'name', 'is_active', 'slug', 'description', 'meta_description'];
    protected $table = 'tags';
    protected $fillable = self::FIELDS;
    public function productTags()
    {
        return $this->hasMany(ProductTags::class, 'tags_id')->with('products');
    }

    /**
     * Get list category to product
     */
    public static function GetListByProduct($tagIds = array())
    {
        $data = array();
        $fs = self::get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => in_array($item->id, $tagIds) ? true : false,
                );
                $data[] = array(
                    'name' => $item->name,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => [],
                    'slug' => $item->slug,
                );
            }
        }
        return $data;
    }
}
