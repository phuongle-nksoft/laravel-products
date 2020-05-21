<?php

namespace Nksoft\Products\Models;

use Nksoft\Master\Models\NksoftModel;

class Regions extends NksoftModel
{
    const FIELDS = ['id', 'name', 'parent_id', 'is_active', 'order_by', 'slug', 'description', 'video_id', 'type', 'meta_description'];
    protected $table = 'regions';
    protected $fillable = self::FIELDS;

    public function parent()
    {
        return $this->belongsTo(Regions::class, 'parent_id')->with(['images']);
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'regions_id')->where(['is_active' => 1])
            ->select(Products::FIELDS)
            ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
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
                    'name' => $item->name,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => self::GetListCategories(['parent_id' => $item->id], $result),
                    'slug' => $item->slug,
                    'selected' => $item->id === $parentId ? true : false,
                );
            }
        }
        return $data;
    }

    /**
     * Get list category to product
     */
    public static function GetListByProduct($where, $product)
    {
        $parentId = $product->regions_id ?? 0;
        $data = array();
        $fs = self::where($where)->orderBy('order_by')->get();
        if ($fs) {
            foreach ($fs as $item) {
                $selected = array(
                    'opened' => false,
                    'selected' => $item->id === $parentId ? true : false,
                );
                $data[] = array(
                    'name' => $item->name,
                    'icon' => 'fas fa-folder',
                    'id' => $item->id,
                    'state' => $selected,
                    'children' => self::GetListByProduct(['parent_id' => $item->id], $product),
                    'slug' => $item->slug,
                    'selected' => $item->id === $parentId ? true : false,
                );
            }
        }
        return $data;
    }
}
