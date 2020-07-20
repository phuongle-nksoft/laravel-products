<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Articles\Models\Blocks;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Master\Models\Settings;
use Nksoft\Products\Models\Brands;
use Nksoft\Products\Models\Categories;
use Nksoft\Products\Models\CategoryProductsIndex;
use Nksoft\Products\Models\ChildProducts;
use Nksoft\Products\Models\Customers;
use Nksoft\Products\Models\Discovery;
use Nksoft\Products\Models\ProductComments;
use Nksoft\Products\Models\ProductOptional;
use Nksoft\Products\Models\Products as CurrentModel;
use Nksoft\Products\Models\ProductTags;
use Nksoft\Products\Models\ProfessionalRatings;
use Nksoft\Products\Models\Professionals;
use Nksoft\Products\Models\Regions;
use Nksoft\Products\Models\Tags;
use Nksoft\Products\Models\Vintages;
use Nksoft\Products\Models\VintagesProductIndex;
use Nksoft\Products\Models\Wishlists;

class ProductsController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'products';

    protected $excFields = ['images', 'categories_id', 'id', 'vintages_id', 'optionals_name', 'optionals_id', 'optionals_description', 'optionals_video', 'optionals_images', 'optionals_banner', 'optionals_delete'];
    protected $mergFields = ['images', 'categories_id', 'professionals_rating', 'tags', 'vintages_id', 'optionals_name', 'optionals_id', 'optionals_description', 'optionals_video', 'optionals_images', 'optionals_banner', 'optionals_delete', 'product_ids'];

    protected $model = CurrentModel::class;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $columns = [
                ['key' => 'order_by', 'label' => 'STT', 'widthCol' => 80],
                ['key' => 'id', 'label' => 'Id', 'type' => 'hidden'],
                ['key' => 'name', 'label' => trans('nksoft::common.Name')],
                ['key' => 'price', 'label' => trans('nksoft::common.Price'), 'formatter' => 'number'],
                ['key' => 'year_of_manufacture', 'label' => trans('nksoft::common.Year Of Manufacture'), 'data' => $this->getYearOfManufacture()],
                ['key' => 'qty', 'label' => trans('nksoft::common.Qty'), 'data' => null, 'formatter' => 'number'],
                ['key' => 'id', 'label' => trans('nksoft::common.categories'), 'data' => Categories::select(['id', 'name']), 'type' => 'select', 'relationship' => 'first_category', 'childRelationship' => 'categories'],
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => $this->getTypeProducts(), 'type' => 'select'],
            ];
            $select = Arr::pluck($columns, 'key');
            $q = request()->get('q');
            $results = CurrentModel::select($select);
            if ($q && $q != 'null') {
                $results = $results->where('name', 'like', '%' . $q . '%');
            }
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $results->with(['histories', 'firstCategory'])->orderBy('created_at', 'desc')->get(),
                'columns' => $columns,
                'module' => $this->module,
                'listDelete' => CurrentModel::whereIn('id', $listDelete)->get(),
                'showSearch' => true,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $this->formData = \array_merge($this->formData, $this->mergFields);
            $response = [
                'formElement' => $this->formElement(),
                'result' => null,
                'formData' => $this->formData,
                'module' => $this->module,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e->getMessage());
        }
    }

    private function getYearOfManufacture($result = null)
    {
        $date = [
            ['id' => 300, 'name' => 'None Vintage', 'selected' => $result ? $result->year_of_manufacture == 0 : false],
        ];
        for ($i = 0; $i < 200; $i++) {
            $v = date('Y') - $i;
            $date[] = ['id' => $v, 'name' => $v, 'selected' => $result ? $result->year_of_manufacture == $v : false];
        }
        return $date;
    }

    private function formElement($result = null)
    {
        $categories = Categories::GetListByProduct(array('parent_id' => 0), $result ? $result->categoryProductIndies->pluck('categories_id')->toArray() : [0]);
        $vintages = Vintages::GetListByProduct(array('parent_id' => 0), $result ? $result->vintages->pluck('vintages_id')->toArray() : [0]);
        $vintagesBanner = Vintages::select('id', 'name')->get();
        $brands = Brands::select(['id', 'name'])->get();
        $regions = Regions::GetListByProduct(array('parent_id' => 0), $result);
        $professional = Professionals::select(['id', 'name'])->get();

        $custom = [
            [
                'label' => trans('nksoft::common.professionals'),
                'type' => 'select',
                'defaultValue' => $professional,
                'key' => 'professionals_id',
            ],
            [
                'label' => trans('nksoft::common.Rating'),
                'type' => 'number',
                'key' => 'ratings',
                'class' => 'col-12 col-lg-3',
            ],
            [
                'label' => trans('nksoft::common.Content'),
                'type' => 'textarea',
                'key' => 'description',
            ],
            [
                'label' => trans('nksoft::common.Show Comment'),
                'type' => 'checkbox',
                'key' => 'show',
                'class' => 'col-md-1',
            ],
        ];
        $volume = [
            ['id' => 750, 'name' => '750ML', 'selected' => $result ? $result->volume == 750 : false],
            ['id' => 1000, 'name' => '1L', 'selected' => $result ? $result->volume == 1000 : false],
            ['id' => 1500, 'name' => '1.5L', 'selected' => $result ? $result->volume == 1500 : false],
            ['id' => 3000, 'name' => '3L', 'selected' => $result ? $result->volume == 3000 : false],
            ['id' => 6000, 'name' => '6L', 'selected' => $result ? $result->volume == 6000 : false],
        ];

        $tagIds = [];
        if ($result) {
            $tagIds = $result->productTags()->pluck('tags_id')->toArray();
        }

        $tags = Tags::GetListByProduct($tagIds);
        $units = [
            ['id' => 1, 'name' => 'Chiếc'],
            ['id' => 2, 'name' => 'Bộ'],
        ];
        $productIds = $result && $result->product_ids ? $result->product_ids : [];
        $products = CurrentModel::GetListProducts($productIds);
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => $this->getTypeProducts(), 'type' => 'select'],
                    ['key' => 'categories_id', 'label' => trans('nksoft::common.categories'), 'data' => $categories, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'vintages_banner_id', 'label' => trans('nksoft::products.Vintages Content'), 'data' => $vintagesBanner, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'vintages_id', 'label' => trans('nksoft::products.Vintages Title'), 'data' => $vintages, 'class' => 'required', 'multiple' => true, 'type' => 'tree'],
                    ['key' => 'regions_id', 'label' => trans('nksoft::common.regions'), 'data' => $regions, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'brands_id', 'label' => trans('nksoft::common.brands'), 'data' => $brands, 'class' => 'required', 'type' => 'select'],
                ],
                'active' => true,
            ],
            [
                'key' => 'inputForm',
                'label' => trans('nksoft::common.Content'),
                'element' => [
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'sku', 'label' => trans('nksoft::common.Sku'), 'data' => null, 'type' => 'text'],
                    ['key' => 'price', 'label' => trans('nksoft::common.Price'), 'data' => null, 'type' => 'number'],
                    ['key' => 'price_contact', 'label' => trans('nksoft::common.Price Contact'), 'data' => null, 'type' => 'checkbox'],
                    ['key' => 'special_price', 'label' => trans('nksoft::common.Special Price'), 'data' => null, 'type' => 'number'],
                    ['key' => 'unit', 'label' => trans('nksoft::products.Unit'), 'data' => $units, 'type' => 'select'],
                    ['key' => 'qty', 'label' => trans('nksoft::common.Qty'), 'data' => null, 'class' => 'required', 'type' => 'number'],
                    ['key' => 'alcohol_content', 'label' => trans('nksoft::common.Alcohol Content'), 'data' => null, 'type' => 'number'],
                    ['key' => 'scarce', 'label' => 'Rượu Hiếm Có', 'data' => null, 'type' => 'checkbox'],
                    ['key' => 'volume', 'label' => trans('nksoft::common.Volume'), 'data' => $volume, 'type' => 'text'],
                    ['key' => 'smell', 'label' => trans('nksoft::products.Product Detail'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'editor'],
                    ['key' => 'rate', 'label' => trans('nksoft::common.Rate'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'number'],
                    ['key' => 'year_of_manufacture', 'label' => trans('nksoft::common.Year Of Manufacture'), 'data' => $this->getYearOfManufacture($result), 'class' => 'col-12 col-lg-4', 'type' => 'select'],
                    ['key' => 'tags', 'label' => trans('nksoft::common.tags'), 'data' => $tags, 'type' => 'tree', 'multiple' => true],
                    ['key' => 'order_by', 'label' => trans('nksoft::common.Order By'), 'data' => null, 'type' => 'number'],
                    ['key' => 'slug', 'label' => trans('nksoft::common.Slug'), 'data' => null, 'type' => 'text'],
                    ['key' => 'images', 'label' => trans('nksoft::common.Images'), 'data' => null, 'type' => 'image'],
                ],
            ],
            [
                'key' => 'professionals_rating',
                'label' => trans('nksoft::common.Professional Rating'),
                'element' => [
                    ['key' => 'professionals_rating', 'label' => trans('nksoft::common.Button.Add'), 'data' => $custom, 'type' => 'custom'],
                ],
            ],
            [
                'key' => 'childProducts',
                'label' => trans('nksoft::common.Extra'),
                'element' => [
                    ['key' => 'optionals_id', 'label' => '', 'data' => null, 'class' => 'hidden', 'type' => 'hidden'],
                    ['key' => 'optionals_name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => '', 'type' => 'text'],
                    ['key' => 'optionals_description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'editor'],
                    ['key' => 'optionals_video', 'label' => 'Video', 'data' => null, 'type' => 'text'],
                    ['key' => 'optionals_banner', 'label' => trans('nksoft::common.Banner'), 'data' => null, 'type' => 'image'],
                    ['key' => 'optionals_images', 'label' => trans('nksoft::common.Images'), 'data' => null, 'type' => 'image'],
                    ['key' => 'optionals_delete', 'label' => trans('nksoft::common.Button.Delete'), 'data' => null, 'type' => 'checkbox'],
                ],
            ],
            [
                'key' => 'products',
                'label' => trans('nksoft::common.products'),
                'element' => [
                    ['key' => 'product_ids', 'label' => trans('nksoft::common.products'), 'data' => $products, 'multiple' => true, 'type' => 'tree'],
                ],
            ],
            [
                'key' => 'seo',
                'label' => 'SEO',
                'element' => [
                    ['key' => 'canonical_link', 'label' => 'Canonical Link', 'data' => null, 'type' => 'text'],
                    ['key' => 'meta_title', 'label' => 'Title', 'data' => null, 'type' => 'text'],
                    ['key' => 'meta_description', 'label' => trans('nksoft::common.Meta Description'), 'data' => null, 'type' => 'textarea'],
                ],
            ],
        ];
    }

    private function rules()
    {
        $vintages = request()->get('vintages_id');
        $rules = [
            'name' => 'required',
            'categories_id' => 'required',
            'vintages_id' => 'required',
            'vintages_banner_id' => 'required',
            'regions_id' => 'required',
            'brands_id' => 'required',
            'qty' => 'required',
            'images[]' => 'file',
        ];

        return $rules;
    }

    private function message()
    {
        return [
            'name.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Name')]),
            'categories_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.categories')]),
            'vintages_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Vintages Title')]),
            'vintages_banner_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Vintages Content')]),
            'regions_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.regions')]),
            'brands_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.brands')]),
            'sku.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Sku')]),
            'price.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Price')]),
            'alcohol_content.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Alcohol Content')]),
            'qty.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Qty')]),
        ];
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->checkVintages($request)) {
            return $this->responseError([__('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Vintages Title')])]);
        }
        $validator = Validator($request->all(), $this->rules(), $this->message());
        if ($validator->fails()) {
            return $this->responseError($validator->errors());
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!in_array($item, $this->excFields)) {
                    $data[$item] = $request->get($item);
                }
            }
            if ($request->get('duplicate')) {
                $data['slug'] = null;
            }

            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
            $this->setCategoryProductsIndex($request, $result);
            $this->setProfessionalRating($request, $result);
            $this->setVintagesProductsIndex($request, $result);
            $this->setTags($request, $result);
            $this->setOptionals($request, $result);
            $this->setChildProduct($request, $result);
            $this->media($request, $result);
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function setProfessionalRating($request, $result)
    {
        $productsId = $result->id;
        $professionalRating = \json_decode($request->get('professionals_rating'));
        $professionalIds = collect($professionalRating)->pluck('professionals_id')->all();

        /** Delete record by category id not in list */
        $deleteProfessional = ProfessionalRatings::where(['products_id' => $productsId]);
        if (!$professionalIds) {
            $deleteProfessional->whereNotIn('professionals_id', $professionalIds);
        }
        $deleteProfessional->forceDelete();

        /** Save new record */
        if ($professionalRating) {
            foreach ($professionalRating as $data) {
                $dataRatings = [
                    'products_id' => $productsId,
                    'professionals_id' => $data->professionals_id,
                    'description' => $data->description,
                    'ratings' => $data->ratings,
                    'show' => $data->show ?? 0,
                ];
                ProfessionalRatings::updateOrCreate(['professionals_id' => $data->professionals_id, 'products_id' => $productsId], $dataRatings);
            }
        }

    }
    private function setTags($request, $result)
    {
        $tagIds = \json_decode($request->get('tags')) ?? [];
        $tags = ProductTags::where(['products_id' => $result->id]);
        /** Save new record */
        if (count($tagIds) > 0) {
            $tags = $tags->whereNotIn('tags_id', $tagIds);
            foreach ($tagIds as $id) {
                $productTags = [
                    'products_id' => $result->id,
                    'tags_id' => $id,
                ];
                ProductTags::updateOrCreate(['products_id' => $result->id, 'tags_id' => $id], $productTags);
            }
        }
        $tags->forceDelete();
    }

    private function setCategoryProductsIndex(Request $request, $result)
    {
        $categoryIds = \json_decode($request->get('categories_id'));
        if (!is_array($categoryIds)) {
            $categoryIds = array($categoryIds);
        }

        if (!$categoryIds) {
            CategoryProductsIndex::where(['products_id' => $result->id])->forceDelete();
            return;
        }

        /** Delete record by category id not in list */
        CategoryProductsIndex::where(['products_id' => $result->id])->whereNotIn('categories_id', $categoryIds)->forceDelete();

        /** Save new record */
        foreach ($categoryIds as $id) {
            $data = [
                'products_id' => $result->id,
                'categories_id' => $id,
            ];
            CategoryProductsIndex::updateOrCreate(['products_id' => $result->id, 'categories_id' => $id], $data);
        }
    }

    private function checkVintages($request)
    {
        $vintageIds = \json_decode($request->get('vintages_id'));
        if (!is_array($vintageIds)) {
            $vintageIds = array($vintageIds);
        }
        if (count($vintageIds) == 0) {
            return false;
        }
        return true;
    }

    private function setVintagesProductsIndex($request, $result)
    {
        $vintageIds = \json_decode($request->get('vintages_id'));
        if (!is_array($vintageIds)) {
            $vintageIds = array($vintageIds);
        }
        if (count($vintageIds) == 0) {
            return $this->responseError([__('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Vintages Title')])]);
        }

        /** Delete record by category id not in list */
        VintagesProductIndex::where(['products_id' => $result->id])->whereNotIn('vintages_id', $vintageIds)->forceDelete();

        /** Save new record */
        if (count($vintageIds) > 0) {
            foreach ($vintageIds as $id) {
                $data = [
                    'products_id' => $result->id,
                    'vintages_id' => $id,
                ];
                VintagesProductIndex::updateOrCreate(['products_id' => $result->id, 'vintages_id' => $id], $data);
            }
        }

    }

    private function setOptionals($request, $result)
    {
        $delete = $request->get('optionals_delete');
        $name = $request->get('optionals_name');
        $description = $request->get('optionals_description');
        $video_id = $request->get('optionals_video');
        if ($delete || $name == null) {
            ProductOptional::where(['products_id' => $result->id])->forceDelete();
        } else if ($name != null) {
            $data = [
                'name' => $name,
                'description' => $description,
                'video_id' => $video_id,
                'products_id' => $result->id,
            ];
            $data['slug'] = $this->getSlug($data);
            $optional = ProductOptional::updateOrCreate(['products_id' => $result->id], $data);
            $optionalsImages = $request->get('optionals_images');
            if ($optionalsImages) {
                $this->setMedia($optionalsImages, $optional->id, 'product_optionals');
            }
            $optionalsBanner = $request->get('optionals_banner');
            if ($optionalsBanner) {
                $this->setMedia($optionalsBanner, $optional->id, 'product_optionals', 2);
            }
        }

    }

    public function setChildProduct($request, $result)
    {
        $productIds = \json_decode($request->get('product_ids'));
        if (!$productIds) {
            $productIds = array();
        }

        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        /** Delete record by category id not in list */
        ChildProducts::where(['parent_id' => $result->id])->whereNotIn('child_id', $productIds)->forceDelete();
        if (count($productIds) == 0) {
            return;
        }

        /** Save new record */
        if (count($productIds) > 0) {
            foreach ($productIds as $id) {
                $data = [
                    'parent_id' => $result->id,
                    'child_id' => $id,
                ];
                ChildProducts::updateOrCreate(['parent_id' => $result->id, 'child_id' => $id], $data);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $select = CurrentModel::FIELDS;
            $with = ['images', 'firstCategory', 'vintages', 'brands', 'regions', 'professionalsRating', 'vintageBanner', 'productOptional', 'childProducts'];
            $result = CurrentModel::where(['is_active' => 1, 'id' => $id])
                ->select($select)
                ->with($with)->first();
            if (!$result) {
                return $this->responseError('404');
            }

            //get brand products
            $brands = CurrentModel::where(['is_active' => 1, 'brands_id' => $result->brands_id])
                ->where('id', '<>', $id)
                ->select($select)
                ->orderBy('updated_at', 'desc')
                ->with($with)
                ->take(3)->get();
            //get vintages products
            $vintagesId = VintagesProductIndex::where(['products_id' => $id])->pluck('vintages_id');
            $vintages = CurrentModel::whereIn('id', function ($query) use ($vintagesId) {
                $query->from(with(new VintagesProductIndex())->getTable())->select(['products_id'])->whereIn('vintages_id', $vintagesId)->groupBy('products_id')->pluck('products_id');
            })->where(['is_active' => 1])
                ->where('id', '<>', $id)
                ->orderBy('order_by', 'asc')
                ->orderBy('created_at', 'desc')
                ->with($with)
                ->take(3)->get();

            //get regions product
            $regions = CurrentModel::where(['is_active' => 1, 'regions_id' => $result->regions_id])
                ->where('id', '<>', $id)
                ->select($select)
                ->orderBy('order_by', 'asc')
                ->orderBy('created_at', 'desc')
                ->with($with)->take(3)->get();

            //get list other product
            $listCategoryIds = $result->firstCategory()->pluck('categories_id');
            $productInCategory = CurrentModel::whereIn('id', function ($query) use ($listCategoryIds) {
                $query->from(with(new CategoryProductsIndex())->getTable())->select(['products_id'])->whereIn('categories_id', $listCategoryIds)->groupBy('products_id')->pluck('products_id');
            })->where(['is_active' => 1, 'type' => $result->type])
                ->where('id', '<>', $id);
            $price = $result->price;
            if ($price < 10000000) {
                $productInCategory = $productInCategory->where('price', '<=', 10000000);
            }
            if ($price > 10000000 && $price < 30000000) {
                $productInCategory = $productInCategory->whereBetween('price', [10000000, 30000000]);
            }
            if ($price > 30000000) {
                $productInCategory = $productInCategory->where('price', '>=', 30000000);
            }
            $productInCategory = $productInCategory->orderBy('order_by', 'asc')
                ->orderBy('created_at', 'desc')
                ->with($with)
                ->take(15)->get();
            //update views
            CurrentModel::where(['id' => $id])->update(['views' => $result->views + 1]);
            $breadcrumb = [
                ['link' => '', 'label' => \trans('nksoft::common.Home')],
            ];
            $category = $result->firstCategory;
            if ($category) {
                array_push($breadcrumb, ['link' => $category->categories->slug, 'label' => $category->categories->name]);
            }
            array_push($breadcrumb, ['active' => true, 'link' => '#', 'label' => $result->name]);
            $response = [
                'result' => $result,
                'brands' => $brands,
                'vintages' => $vintages,
                'regions' => $regions,
                'productInCategory' => $productInCategory,
                'template' => 'product-detail',
                'breadcrumb' => $breadcrumb,
                'seo' => $this->SEO($result),
            ];
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $result = CurrentModel::select($this->formData)->with(['images', 'categoryProductIndies', 'professionalsRating', 'vintages', 'productOptional', 'childProducts'])->find($id);
            $this->formData = \array_merge($this->formData, $this->mergFields);
            $result->categories_id = $result->categoryProductIndies->pluck('categories_id')->toArray();
            $result->vintages_id = $result->vintages->pluck('vintages_id')->toArray();
            $result->tags = $result->productTags->pluck('tags_id')->toArray();
            $result->product_ids = $result->childProducts->pluck('child_id')->toArray();
            // set optional to product
            $optional = $result->productOptional;
            if ($optional) {
                $result->optionals_name = $optional->name ?? '';
                $result->optionals_description = $optional->description ?? '';
                $result->optionals_video = $optional->video_id ?? '';
                $result->optionals_images = $optional->images()->where(['group_id' => 1])->get();
                $result->optionals_banner = $optional->images()->where(['group_id' => 2])->get();
            }
            $response = [
                'formElement' => $this->formElement($result),
                'result' => $result,
                'formData' => $this->formData,
                'module' => $this->module,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->checkVintages($request)) {
            return $this->responseError([__('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Vintages Title')])]);
        }
        $result = CurrentModel::find($id);
        if ($result == null) {
            return $this->responseError();
        }
        $validator = Validator($request->all(), $this->rules($id), $this->message());
        if ($validator->fails()) {
            return $this->responseError($validator->errors());
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!in_array($item, $this->excFields)) {
                    $data[$item] = $request->get($item);
                    if ($data[$item] === 'undefined') {
                        $data[$item] = null;
                    }
                }
            }
            $data['slug'] = $this->getSlug($data);
            foreach ($data as $k => $v) {
                $result->$k = $v;
            }
            $result->save();
            $this->setUrlRedirects($result);
            $this->setCategoryProductsIndex($request, $result);
            $this->setProfessionalRating($request, $result);
            $this->setTags($request, $result);
            $this->setVintagesProductsIndex($request, $result);
            $this->setOptionals($request, $result);
            $this->setChildProduct($request, $result);
            $this->media($request, $result);
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function addWishlist(Request $request)
    {
        try {
            $user = session('user');
            $validator = Validator($request->all(), ['products_id' => 'required'], ['products_id' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Product Id')])]);
            if ($validator->fails() || !$user) {
                return $this->responseError($validator->errors());
            }
            $productId = $request->get('products_id');
            Wishlists::updateOrCreate(['customers_id' => $user->id, 'products_id' => $productId], ['customers_id' => $user->id, 'products_id' => $productId]);
            $customer = Customers::where(['id' => $user->id])->with(['shipping', 'wishlists', 'orders'])->first();
            session(['user' => $customer]);
            return $this->responseViewSuccess(['user' => $customer], ['Sản phẩm đã được thêm vào danh sách rượu của bạn']);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getComment($productId)
    {
        try {
            $comment = ProductComments::where(['products_id' => $productId, 'parent_id' => 0, 'status' => 1])->with(['children'])->orderBy('id', 'desc')->paginate();
            return $this->responseViewSuccess(['comment' => $comment, 'block' => Blocks::where(['identify' => 'product-detail-question', 'is_active' => 1])->first()]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $user = session('user');
            if (!$user) {
                $user = Customers::where(['email' => 'khachonline@gmail.com'])->first();
            }

            $rules = [
                'products_id' => 'required',
                'description' => 'required',
            ];
            $messages = [
                'products_id' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Product Id')]),
                'description' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Content')]),
            ];
            $validator = Validator($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return $this->responseError($validator->errors());
            }
            $productId = $request->get('products_id');
            $data = [
                'customers_id' => $user->id,
                'products_id' => $productId,
                'description' => $request->get('description'),
                'status' => 0,
                'parent_id' => $request->get('parent_id'),
                'name' => $user->name,
            ];
            ProductComments::create($data);
            $comment = ProductComments::where(['products_id' => $productId, 'parent_id' => 0, 'status' => 1])->with(['children'])->orderBy('id', 'desc')->paginate();
            return $this->responseViewSuccess(['comment' => $comment], ['Câu hỏi của bạn đã được gửi cho chúng tôi']);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function deleteWishlist($wishlistId)
    {
        try {
            $user = session('user');
            $wishlist = Wishlists::where(['customers_id' => $user->id])->where('id', '<>', $wishlistId)->get();
            Wishlists::where('id', $wishlistId)->forceDelete();
            return $this->responseViewSuccess(['wishlist' => $wishlist]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getHome()
    {
        try {
            $ads = Blocks::where(['is_active' => 1])->with(['images'])->get();
            $tags = Tags::offset(0)->take(4)->with(['productTags']);
            $tagIds = $tags->pluck('id')->toArray();
            $products = CurrentModel::whereIn('id', function ($query) use ($tagIds) {
                $query->from(with(new ProductTags())->getTable())->select(['products_id'])->whereIn('tags_id', $tagIds)->pluck('products_id');
            })->where(['is_active' => 1])->with(['professionalsRating', 'images', 'productTags', 'firstCategory'])->orderBy('updated_at', 'desc')->get();
            $setting = Settings::first();
            $settingImage = $setting->images()->first();
            $img = $settingImage ? url('storage/' . $settingImage->image) : url('wine/images/share/logo.svg');
            $result = [
                'ads' => $ads,
                'result' => $products,
                'tags' => $tags->get(),
                'seo' => [
                    'title' => $setting->title,
                    'ogDescription' => $setting->description,
                    'ogUrl' => url('/'),
                    'ogImage' => $img,
                    'ogSiteName' => $setting->title,
                ],
            ];
            return $this->responseViewSuccess($result);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getSearch()
    {
        $q = request()->get('q');
        try {
            $products = CurrentModel::where(['is_active' => 1])->where('name', 'like', '%' . $q . '%')
                ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
            $response = [
                'result' => null,
                'products' => $products->paginate(),
                'total' => $products->count(),
                'banner' => null,
                'template' => 'products',
                'breadcrumb' => [
                    ['link' => '', 'label' => \trans('nksoft::common.Home')],
                    ['active' => true, 'link' => '#', 'label' => $q],
                ],
            ];
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function filter($slug)
    {
        try {
            $result = Discovery::where(['slug' => $slug, 'is_active' => 1])->with(['images'])->first();
            if (!$result) {
                return $this->responseError('404');
            }
            $condition = collect(config('nksoft.conditionFilter'))->firstWhere('id', $result->condition);
            $productId = [];
            if ($result->type == 'professional') {
                $productId = ProfessionalRatings::select(['products_id'])->where($result->key, $condition['condition'], $result->value)->groupBy('products_id')->pluck('products_id');
            }
            $products = CurrentModel::where(['is_active' => 1, 'type' => 1])->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
            if (count($productId) > 0) {
                $products = $products->whereIn('id', $productId);
            }
            if ($result->type == 'products') {
                if ($result->condition) {
                    $products = $products->where($result->key, $condition['condition'], $result->value);
                } else {
                    $products = $products->where($result->key, $result->value);
                }
            }
            $allRequest = request()->all();
            if (isset($allRequest['vg'])) {
                $vingateId = $allRequest['vg'];
                $products = $products->whereIn('id', function ($query) use ($vingateId) {
                    $query->from(with(new VintagesProductIndex())->getTable())->select(['products_id'])->where('vintages_id', $vingateId)->pluck('products_id');
                });
            }
            if (isset($allRequest['c'])) {
                $categoryId = $allRequest['c'];
                $products = $products->whereIn('id', function ($query) use ($categoryId) {
                    $query->from(with(new CategoryProductsIndex())->getTable())->select(['products_id'])->where('categories_id', $categoryId)->pluck('products_id');
                });
            }
            if (isset($allRequest['rg'])) {
                $provinceId = $allRequest['rg'];
                $regionId = Regions::GetListIds(['id' => $provinceId]);
                $products = $products->whereIn('regions_id', $regionId);
            }
            if (isset($allRequest['r'])) {
                $regionId = $allRequest['r'];
                $products = $products->where(['regions_id' => $regionId]);
            }
            if (isset($allRequest['p'])) {
                $rating = $allRequest['p'];
                $products = $products->whereIn('id', function ($query) use ($rating) {
                    $query->from(with(new ProfessionalRatings())->getTable())->select(['products_id'])->where('ratings', $rating)->pluck('products_id');
                });
            }
            if (isset($allRequest['v'])) {
                $volume = $allRequest['v'];
                $products = $products->where(['volume' => $volume]);
            }
            if (isset($allRequest['sort'])) {
                $sort = $allRequest['sort'];
                $condition = explode('-', $sort);
                $products = $products->orderBy($condition[0], $condition[1]);
            } else {
                $products = $products->orderBy('price', 'asc');
            }
            if (isset($allRequest['qty'])) {
                $qty = $allRequest['qty'];
                $products = $products->where('qty', $qty == 1 ? '<' : '>', 5);
            }
            if (isset($allRequest['pr'])) {
                $pr = $allRequest['pr'];
                $condition = explode('-', $pr);
                $products = $products->where('price', '>=', $condition[0] * 1000);
                if (isset($condition[1])) {
                    $products = $products->where('price', '<=', $condition[1] * 1000);
                }
            }
            $response = [
                'result' => $result,
                'products' => $products->paginate(),
                'total' => $products->count(),
                'banner' => $result->images()->where(['group_id' => 2])->first(),
                'template' => 'products',
                'breadcrumb' => [
                    ['link' => '', 'label' => \trans('nksoft::common.Home')],
                    ['active' => true, 'link' => '#', 'label' => $result->name],
                ],
                'seo' => $this->SEO($result),
                'filter' => $this->listFilter(1, $products),
            ];
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }
}
