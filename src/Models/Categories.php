<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Categories extends NksoftModel
{
    protected $table = 'categories';
    protected $fillable = ['id', 'name', 'parent_id', 'is_active', 'order_by', 'slug', 'description', 'video_id', 'meta_description'];
    public function parentId()
    {
        return $this->belongsTo('\Nksoft\Products\Models\Categories', 'parent_id');
    }
    /**
     * Get list category with recursive
     */
    public static function GetListCategories($where, $result)
    {
        $parentId = $result->parent_id ?? 0;
        $id = $result->id ?? 0;
        $data = array();
        $fs = self::where($where)->where('id', '<>', $id)->orderBy('order_by')->get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => $item->id === $parentId ? true : false,
                );
                $data[] = array(
                    'text' => $item->name,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => self::GetListCategories(['parent_id' => $item->id], $result),
                    'slug' => $item->slug,
                );
            }
        }
        return $data;
    }

    /**
     * Get list category to product
     */
    public static function GetListByProduct($where, array $idSelected)
    {
        // $parentId = $product ? $product->categoryProductIndies->pluck('categories_id') : 0;
        $data = array();
        $fs = self::where($where)->orderBy('order_by')->get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => in_array($item->id, $idSelected) ? true : false,
                );
                $data[] = array(
                    'text' => $item->name,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => self::GetListByProduct(['parent_id' => $item->id], $idSelected),
                    'slug' => $item->slug,
                );
            }
        }
        return $data;
    }
}
