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
use Nksoft\Products\Models\Customers;
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
    protected $mergFields = ['images', 'categories_id', 'professionals_rating', 'tags', 'vintages_id', 'optionals_name', 'optionals_id', 'optionals_description', 'optionals_video', 'optionals_images', 'optionals_banner', 'optionals_delete'];

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
                ['key' => 'id', 'label' => 'Id', 'type' => 'hidden'],
                ['key' => 'name', 'label' => trans('nksoft::common.Name')],
                ['key' => 'price', 'label' => trans('nksoft::common.Price'), 'formatter' => 'number'],
                ['key' => 'year_of_manufacture', 'label' => trans('nksoft::common.Year Of Manufacture'), 'data' => $this->getYearOfManufacture()],
                ['key' => 'qty', 'label' => trans('nksoft::common.Qty'), 'data' => null, 'formatter' => 'number'],
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
            ];
            $select = Arr::pluck($columns, 'key');
            $q = request()->get('q');
            if ($q) {
                $results = CurrentModel::select($select)->where('name', 'like', '%' . $q . '%')->with(['histories'])->get();
            } else {
                $results = CurrentModel::select($select)->with(['histories'])->orderBy('updated_at', 'desc')->paginate();
            }
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $results,
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
            ['id' => 0, 'name' => 'None Vintage', 'selected' => $result ? $result->year_of_manufacture == 0 : false],
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
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => config('nksoft.productType'), 'type' => 'select'],
                    ['key' => 'categories_id', 'label' => trans('nksoft::common.categories'), 'data' => $categories, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'vintages_banner_id', 'label' => trans('nksoft::products.Vintages Content'), 'data' => $vintagesBanner, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'vintages_id', 'label' => trans('nksoft::products.Vintages Title'), 'data' => $vintages, 'class' => 'required', 'multiple' => true, 'type' => 'tree'],
                    ['key' => 'regions_id', 'label' => trans('nksoft::common.regions'), 'data' => $regions, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'brands_id', 'label' => trans('nksoft::common.brands'), 'data' => $brands, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'meta_description', 'label' => trans('nksoft::common.Meta Description'), 'data' => null, 'type' => 'textarea'],
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
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
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
        $images = $request->file('optionals_images');
        $banner = $request->file('optionals_banner');
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
            if ($request->hasFile('optionals_images')) {
                $this->setMedia($images, $optional->id, 'product_optionals');
            }
            if ($request->hasFile('optionals_banner')) {
                $this->setMedia($banner, $optional->id, 'product_optionals', 2);
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
            $with = ['images', 'firstCategory', 'vintages', 'brands', 'regions', 'professionalsRating', 'vintageBanner', 'productOptional'];
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
            $listCategoryIds = CategoryProductsIndex::where(['products_id' => $id])->pluck('categories_id');
            $productInCategory = CurrentModel::whereIn('id', function ($query) use ($listCategoryIds) {
                $query->from(with(new CategoryProductsIndex())->getTable())->select(['products_id'])->whereIn('categories_id', $listCategoryIds)->groupBy('products_id')->pluck('products_id');
            })->where(['is_active' => 1])
                ->where('id', '<>', $id)
                ->orderBy('order_by', 'asc')
                ->orderBy('created_at', 'desc')
                ->with($with)
                ->take(15)->get();
            $category = $result->categoryProductIndies->first();
            //update views
            CurrentModel::where(['id' => $id])->update(['views' => $result->views + 1]);
            $image = $result->images()->where(['group_id' => 1])->first();
            $breadcrumb = [
                ['link' => '', 'label' => \trans('nksoft::common.Home')],
            ];
            if ($category) {
                array_push($breadcrumb, ['link' => $category->categories->slug, 'label' => $category->categories->name]);
            }
            // if ($result->regions) {
            //     if ($result->regions->parent) {
            //         array_push($breadcrumb, ['link' => $result->regions->parent->slug, 'label' => $result->regions->parent->name]);
            //     }
            //     array_push($breadcrumb, ['link' => $result->regions->slug, 'label' => $result->regions->name]);
            // }
            // if ($result->brands) {
            //     array_push($breadcrumb, ['link' => $result->brands->slug, 'label' => $result->brands->name]);
            // }
            array_push($breadcrumb, ['active' => true, 'link' => '#', 'label' => $result->name]);
            $response = [
                'result' => $result,
                'brands' => $brands,
                'vintages' => $vintages,
                'regions' => $regions,
                'productInCategory' => $productInCategory,
                'template' => 'product-detail',
                'breadcrumb' => $breadcrumb,
                'seo' => [
                    'title' => $result->name,
                    'ogDescription' => $result->meta_description,
                    'ogUrl' => url($category->categories->slug . '/' . $result->slug),
                    'ogImage' => url('storage/' . $image->image),
                    'ogSiteName' => $result->name,
                ],
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
            $result = CurrentModel::select($this->formData)->with(['images', 'categoryProductIndies', 'professionalsRating', 'vintages', 'productOptional'])->find($id);
            $this->formData = \array_merge($this->formData, $this->mergFields);
            $result->categories_id = $result->categoryProductIndies->pluck('categories_id')->toArray();
            $result->vintages_id = $result->vintages->pluck('vintages_id')->toArray();
            $result->tags = $result->productTags->pluck('tags_id')->toArray();
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
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
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
            $filter = config('nksoft.filterCustom');
            $item = collect($filter)->firstWhere('slug', $slug);
            $productId = [];
            if ($item['type'] == 'professional') {
                $productId = ProfessionalRatings::select(['products_id'])->where($item['key'], '>=', $item['value'])->groupBy('products_id')->pluck('products_id');
            }
            $products = CurrentModel::where(['is_active' => 1, 'type' => 1])->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
            if (count($productId) > 0) {
                $products = $products->whereIn('id', $productId);
            }
            if ($item['type'] == 'products') {
                if (isset($item['condition']) && $item['condition'] == 'gt') {
                    $products = $products->where($item['key'], '>=', $item['value']);
                } else {
                    $products = $products->where($item['key'], $item['value']);
                }
            }
            $allRequest = request()->all();
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
            $im = 'wine/images/share/logo.svg';
            $response = [
                'result' => $item,
                'products' => $products->paginate(),
                'total' => $products->count(),
                'banner' => null,
                'template' => 'products',
                'breadcrumb' => [
                    ['link' => '', 'label' => \trans('nksoft::common.Home')],
                    ['active' => true, 'link' => '#', 'label' => $item['text']],
                ],
                'seo' => [
                    'title' => $item['text'],
                    'ogDescription' => $item['text'],
                    'ogUrl' => url($item['slug']),
                    'ogImage' => url($im),
                    'ogSiteName' => $item['text'],
                ],
            ];
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }
}
