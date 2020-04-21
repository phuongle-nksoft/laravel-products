<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Articles\Models\Blocks;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Brands;
use Nksoft\Products\Models\Categories;
use Nksoft\Products\Models\CategoryProductsIndex;
use Nksoft\Products\Models\ProductComments;
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
    private $formData = ['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'order_by', 'video_id', 'price', 'smell', 'rate', 'special_price', 'year_of_manufacture', 'alcohol_content', 'volume', 'slug', 'description', 'meta_description'];

    protected $module = 'products';

    protected $excFields = ['images', 'categories_id', 'id', 'vintages_id'];
    protected $mergFields = ['images', 'categories_id', 'professionals_rating', 'tags', 'vintages_id'];

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
                ['key' => 'id', 'label' => 'Id'],
                ['key' => 'name', 'label' => trans('nksoft::common.Name')],
                ['key' => 'price', 'label' => trans('nksoft::common.Price')],
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status()],
            ];
            $select = Arr::pluck($columns, 'key');
            $results = CurrentModel::select($select)->with(['histories'])->paginate();
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $results,
                'columns' => $columns,
                'module' => $this->module,
                'listDelete' => CurrentModel::whereIn('id', $listDelete)->get(),
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

    private function formElement($result = null)
    {
        $categories = Categories::GetListByProduct(array('parent_id' => 0), $result ? $result->categoryProductIndies->pluck('categories_id')->toArray() : [0]);
        $vintages = Vintages::GetListByProduct(array('parent_id' => 0), $result ? $result->vintages->pluck('vintages_id')->toArray() : [0]);
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
                'label' => trans('nksoft::common.Content'),
                'type' => 'checkbox',
                'key' => 'show',
                'class' => 'col-md-1',
            ],
        ];
        $volume = [
            ['id' => 750, 'name' => '750ML'],
            ['id' => 1000, 'name' => '1L'],
            ['id' => 1500, 'name' => '1.5L'],
            ['id' => 3000, 'name' => '3L'],
            ['id' => 6000, 'name' => '6L'],
        ];
        $date = [];
        for ($i = 0; $i < 200; $i++) {
            $v = date('Y') - $i;
            $date[] = ['id' => $v, 'name' => $v];
        }
        $tagIds = [];
        if ($result) {
            $tagIds = $result->productTags()->pluck('tags_id')->toArray();
        }

        $tags = Tags::GetListByProduct($tagIds);
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'categories_id', 'label' => trans('nksoft::common.categories'), 'data' => $categories, 'class' => 'required', 'type' => 'tree'],
                    ['key' => 'vintages_id', 'label' => trans('nksoft::common.vintages'), 'data' => $vintages, 'class' => 'required', 'multiple' => true, 'type' => 'tree'],
                    ['key' => 'regions_id', 'label' => trans('nksoft::common.regions'), 'data' => $regions, 'class' => 'required', 'type' => 'tree'],
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
                    ['key' => 'special_price', 'label' => trans('nksoft::common.Special Price'), 'data' => null, 'type' => 'number'],
                    ['key' => 'alcohol_content', 'label' => trans('nksoft::common.Alcohol Content'), 'data' => null, 'type' => 'number'],
                    ['key' => 'volume', 'label' => trans('nksoft::common.Volume'), 'data' => $volume, 'type' => 'select'],
                    ['key' => 'smell', 'label' => trans('nksoft::common.Smell'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'editor'],
                    ['key' => 'rate', 'label' => trans('nksoft::common.Rate'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'number'],
                    ['key' => 'year_of_manufacture', 'label' => trans('nksoft::common.Year Of Manufacture'), 'data' => $date, 'class' => 'col-12 col-lg-4', 'type' => 'select'],
                    ['key' => 'description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'type' => 'editor'],
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
                    // ['key' => 'child_product', 'label' => trans('nksoft::common.Child Product'), 'data' => null, 'type' => 'tree', 'multiple' => true],
                    ['key' => 'tags', 'label' => trans('nksoft::common.tags'), 'data' => $tags, 'type' => 'tree', 'multiple' => true],
                ],
            ],
        ];
    }

    private function rules()
    {
        $rules = [
            'name' => 'required',
            'categories_id' => 'required',
            'vintages_id' => 'required',
            'regions_id' => 'required',
            'brands_id' => 'required',
            'images[]' => 'file',
        ];

        return $rules;
    }

    private function message()
    {
        return [
            'name.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Name')]),
            'categories_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.categories')]),
            'vintages_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.vintages')]),
            'regions_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.regions')]),
            'brands_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.brands')]),
            'sku.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Sku')]),
            'price.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Price')]),
            'alcohol_content.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Alcohol Content')]),
            'volume.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Volume')]),
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
        $validator = Validator($request->all(), $this->rules(), $this->message());
        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'message' => $validator->errors()]);
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!in_array($item, $this->excFields)) {
                    $data[$item] = $request->get($item);
                }
            }
            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
            $this->setCategoryProductsIndex($request, $result);
            $this->setProfessionalRating($request, $result);
            $this->setVintagesProductsIndex($request, $result);
            $this->setTags($request, $result);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    public function setProfessionalRating($request, $result)
    {
        $productsId = $result->id;
        $professionalRating = \json_decode($request->get('professionals_rating'));
        $professionalIds = collect($professionalRating)->pluck('professionals_id')->all();
        if (!$professionalIds) {
            return;
        }

        /** Delete record by category id not in list */
        ProfessionalRatings::where(['products_id' => $productsId])->whereNotIn('professionals_id', $professionalIds)->forceDelete();

        /** Save new record */
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
    private function setTags($request, $result)
    {
        $tagIds = \json_decode($request->get('tags'));
        if (!$tagIds) {
            return;
        }

        /** Delete record by category id not in list */
        ProductTags::where(['products_id' => $result->id])->whereNotIn('tags_id', $tagIds)->forceDelete();
        /** Save new record */

        foreach ($tagIds as $id) {
            $productTags = [
                'products_id' => $result->id,
                'tags_id' => $id,
            ];
            ProductTags::updateOrCreate(['products_id' => $result->id, 'tags_id' => $id], $productTags);
        }
    }

    private function setCategoryProductsIndex(Request $request, $result)
    {
        $categoryIds = \json_decode($request->get('categories_id'));
        if (!is_array($categoryIds)) {
            $categoryIds = array($categoryIds);
        }

        if (!$categoryIds) {
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

    private function setVintagesProductsIndex($request, $result)
    {
        $vintageIds = \json_decode($request->get('vintages_id'));
        if (!is_array($vintageIds)) {
            $vintageIds = array($vintageIds);
        }

        if (!$vintageIds) {
            return;
        }

        /** Delete record by category id not in list */
        VintagesProductIndex::where(['products_id' => $result->id])->whereNotIn('vintages_id', $vintageIds)->forceDelete();

        /** Save new record */
        foreach ($vintageIds as $id) {
            $data = [
                'products_id' => $result->id,
                'vintages_id' => $id,
            ];
            VintagesProductIndex::updateOrCreate(['products_id' => $result->id, 'vintages_id' => $id], $data);
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
            $select = ['id', 'name', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description'];
            $with = ['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating'];
            $result = CurrentModel::where(['is_active' => 1, 'id' => $id])
                ->select($select)
                ->with($with)->first();
            if (!$result) {
                return $this->responseError('404');
            }
            $brands = CurrentModel::where(['is_active' => 1, 'brands_id' => $result->brands_id])->where('id', '<>', $id)
                ->select($select)
                ->orderBy('updated_at', 'desc')
                ->with($with)->paginate();
            $vintages = VintagesProductIndex::where(['vintages_id' => $result->vintages_id])->where('products_id', '<>', $id)
                ->select($select)
                ->orderBy('updated_at', 'desc')
                ->with($with)->paginate();
            $regions = CurrentModel::where(['is_active' => 1, 'regions_id' => $result->regions_id])->where('id', '<>', $id)
                ->select($select)
                ->orderBy('updated_at', 'desc')
                ->with($with)->paginate();
            $productInCategory = CategoryProductsIndex::whereIn('categories_id', function ($query) use ($id) {
                return $query->select('categories_id')->from(with(new CategoryProductsIndex)->getTable())->where(['products_id' => $id])->pluck('categories_id')->toArray();
            })->select(['products_id'])->groupBy('products_id')->with(['products'])->get();
            CurrentModel::where(['id' => $id])->update(['views' => $result->views + 1]);
            $response = [
                'result' => $result,
                'brands' => $brands,
                'vintages' => $vintages,
                'regions' => $regions,
                'productInCategory' => $productInCategory,
                'template' => 'product-detail',
                'breadcrumb' => [
                    ['link' => '/', 'label' => \trans('nksoft::common.Home')],
                    ['active' => true, 'link' => '#', 'label' => $result->name],
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
            $result = CurrentModel::select($this->formData)->with(['images', 'categoryProductIndies', 'professionalsRating', 'vintages'])->find($id);
            $this->formData = \array_merge($this->formData, $this->mergFields);
            $result->categories_id = $result->categoryProductIndies->pluck('categories_id')->toArray();
            $result->vintages_id = $result->vintages->pluck('vintages_id')->toArray();
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
        $result = CurrentModel::find($id);
        if ($result == null) {
            return $this->responseError();
        }
        $validator = Validator($request->all(), $this->rules($id), $this->message());
        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'message' => $validator->errors()]);
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!in_array($item, $this->excFields)) {
                    $data[$item] = $request->get($item);
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
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
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
            $wishlist = Wishlists::updateOrCreate(['customers_id' => $user->id, 'products_id' => $productId], ['customers_id' => $user->id, 'products_id' => $productId]);
            return $this->responseViewSuccess(['wishlist' => $wishlist], ['Sản phẩm đã được thêm vào danh sách rượu của bạn']);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getComment($productId)
    {
        try {
            $comment = ProductComments::where(['products_id' => $productId, 'parent_id' => 0])->with(['children'])->orderBy('id', 'desc')->paginate();
            return $this->responseViewSuccess(['comment' => $comment]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $user = session('user');
            $rules = [
                'products_id' => 'required',
                'description' => 'required',
            ];
            $messages = [
                'products_id' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Product Id')]),
                'description' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Content')]),
            ];
            $validator = Validator($request->all(), $rules, $messages);
            if ($validator->fails() || !$user) {
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
            $comment = ProductComments::where(['products_id' => $productId, 'parent_id' => 0])->with(['children'])->orderBy('id', 'desc')->paginate();
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
            $ads = Blocks::whereIn('identify', ['campaign1', 'campaign2', 'campaign3', 'campaign4'])->where(['is_active' => 1])->get();
            $tags = Tags::take(4)->getQuery();
            $tagIds = $tags->pluck('id')->toArray();
            $products = CurrentModel::whereIn('id', function ($query) use ($tagIds) {
                $query->from(with(new ProductTags())->getTable())->select(['products_id'])->whereIn('tags_id', $tagIds)->pluck('products_id');
            })->where(['is_active' => 1])->with(['professionalsRating', 'images', 'productTags'])->paginate();
            $result = [
                'ads' => $ads,
                'result' => $products,
                'tags' => $tags->get(),
            ];
            return $this->responseViewSuccess($result);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function listFilter()
    {
        dd('list');
    }
}
