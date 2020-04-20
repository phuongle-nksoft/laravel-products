<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
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
use Nksoft\Products\Models\Wishlists;

class ProductsController extends WebController
{
    private $formData = ['id', 'name', 'vintages_id', 'regions_id', 'brands_id', 'sku', 'is_active', 'order_by', 'video_id', 'price', 'smell', 'rate', 'special_price', 'year_of_manufacture', 'alcohol_content', 'volume', 'slug', 'description', 'meta_description'];

    protected $module = 'products';

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
            \array_push($this->formData, 'images');
            \array_push($this->formData, 'categories_id');
            \array_push($this->formData, 'professionals_rating');
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
        $vintages = Vintages::GetListByProduct(array('parent_id' => 0), $result);
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
            $v = date('YYYY') - $i;
            $date[] = ['id' => $v, 'name' => $v];
        }
        $tags = Tags::select(['id', 'name'])->get();
        dd($tags);
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'categories_id', 'label' => trans('nksoft::common.categories'), 'data' => $categories, 'class' => 'required', 'multiple' => true, 'type' => 'tree'],
                    ['key' => 'vintages_id', 'label' => trans('nksoft::common.vintages'), 'data' => $vintages, 'class' => 'required', 'type' => 'tree'],
                    ['key' => 'regions_id', 'label' => trans('nksoft::common.regions'), 'data' => $regions, 'class' => 'required', 'type' => 'tree'],
                    ['key' => 'brands_id', 'label' => trans('nksoft::common.brands'), 'data' => $brands, 'class' => 'required', 'type' => 'select'],
                    ['key' => 'meta_description', 'label' => trans('nksoft::common.Meta Description'), $tags => null, 'type' => 'textarea'],
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
                    ['key' => 'tags', 'label' => trans('nksoft::common.Tags'), 'data' => $tags, 'type' => 'tree', 'multiple' => true],
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
                if (!in_array($item, ['images', 'categories_id'])) {
                    $data[$item] = $request->get($item);
                }
            }
            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
            $this->setCategoryProductsIndex($request, $result);
            $this->setProfessionalRating($request, $result);
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

    private function setTags($request, $result)
    {
        $categoryIds = \json_decode($request->get('tags'));
        $categoryProducts = ProductTags::where(['products_id' => $result->id]);
        /** Delete record by category id not in list */
        foreach ($categoryProducts->get() as $categoryProduct) {
            if (!in_array($categoryProduct->categories_id, $categoryIds)) {
                $categoryProduct->forceDelete();
            }

        }
        /** Save new record */
        $existsItem = $categoryProducts->pluck('tags_id')->toArray();
        foreach ($categoryIds as $id) {
            if (count($existsItem) == 0 || !in_array($id, $existsItem)) {
                $categoryProductIndex = new ProductTags();
                $categoryProductIndex->products_id = $result->id;
                $categoryProductIndex->tags_id = $id;
                $categoryProductIndex->save();
            }
        }
    }

    private function setCategoryProductsIndex(Request $request, $result)
    {
        $categoryIds = \json_decode($request->get('categories_id'));
        $categoryProducts = CategoryProductsIndex::where(['products_id' => $result->id]);
        /** Delete record by category id not in list */
        foreach ($categoryProducts->get() as $categoryProduct) {
            if (!in_array($categoryProduct->categories_id, $categoryIds)) {
                $categoryProduct->forceDelete();
            }

        }
        /** Save new record */
        $existsItem = $categoryProducts->pluck('categories_id')->toArray();
        foreach ($categoryIds as $id) {
            if (count($existsItem) == 0 || !in_array($id, $existsItem)) {
                $categoryProductIndex = new CategoryProductsIndex();
                $categoryProductIndex->products_id = $result->id;
                $categoryProductIndex->categories_id = $id;
                $categoryProductIndex->save();
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
            $select = ['id', 'name', 'vintages_id', 'regions_id', 'brands_id', 'sku', 'is_active', 'video_id', 'order_by', 'price', 'special_price', 'alcohol_content', 'smell', 'rate', 'year_of_manufacture', 'volume', 'slug', 'description', 'meta_description'];
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
            $vintages = CurrentModel::where(['is_active' => 1, 'vintages_id' => $result->vintages_id])->where('id', '<>', $id)
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
                    ['link' => url('/'), 'label' => \trans('nksoft::common.Home')],
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
            $result = CurrentModel::select($this->formData)->with(['images', 'categoryProductIndies', 'professionalsRating'])->find($id);
            \array_push($this->formData, 'images');
            \array_push($this->formData, 'categories_id');
            \array_push($this->formData, 'professionals_rating');
            $result->categories_id = $result->categoryProductIndies->pluck('categories_id')->toArray();
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
                if (!in_array($item, ['images', 'categories_id', 'id'])) {
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
        $dataProducts = ProfessionalRatings::where(['products_id' => $productsId]);
        /** Delete record by category id not in list */
        foreach ($dataProducts->get() as $data) {
            if (!in_array($data->professionals_id, $professionalIds)) {
                $data->forceDelete();
            }

        }
        /** Save new record */
        $existsItem = $dataProducts->pluck('professionals_id')->toArray();
        foreach ($professionalRating as $data) {
            $dataRatings = [
                'products_id' => $productsId,
                'professionals_id' => $data->professionals_id,
                'description' => $data->description,
                'ratings' => $data->ratings,
            ];
            ProfessionalRatings::updateOrCreate(['professionals_id' => $data->professionals_id], $dataRatings);

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

    public function listFilter()
    {
        dd('list');
    }

}
