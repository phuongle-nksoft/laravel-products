<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Tags extends NksoftModel
{
    protected $table = 'tags';
    protected $fillable = ['id', 'name', 'is_active', 'slug', 'description', 'meta_description'];
    public function productTags()
    {
        return $this->belongsTo(ProductTags::class, 'tags_id');
    }

    /**
     * Get list category to product
     */
    public static function GetListByProduct($where, $product)
    {
        $data = array();
        $id = $result->id ?? 0;
        $fs = self::get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => $item->id === $id ? true : false,
                );
                $data[] = array(
                    'text' => $item->name,
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
