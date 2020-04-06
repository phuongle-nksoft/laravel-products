<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Brands;
use Nksoft\Products\Models\Categories;
use Nksoft\Products\Models\CategoryProductsIndex;
use Nksoft\Products\Models\Products as CurrentModel;
use Nksoft\Products\Models\Professionals;
use Nksoft\Products\Models\Regions;
use Nksoft\Products\Models\Vintages;
use Str;

class ProductsController extends WebController
{
    private $formData = ['id', 'name', 'vintages_id', 'regions_id', 'brands_id', 'sku', 'is_active', 'order_by', 'video_id', 'price', 'smell', 'rate', 'special_price', 'professionals_rating', 'year_of_manufacture', 'alcohol_content', 'volume', 'slug', 'description', 'meta_description'];

    protected $module = 'products';
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
                'key' => 'professional',
            ],
            [
                'label' => trans('nksoft::common.Rating'),
                'type' => 'number',
                'key' => 'rating',
                'class' => 'col-12 col-lg-3',
            ],
            [
                'label' => trans('nksoft::common.Content'),
                'type' => 'textarea',
                'key' => 'content',
            ],
        ];
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'categories_id', 'label' => trans('nksoft::common.categories'), 'data' => $categories, 'multiple' => true, 'type' => 'tree'],
                    ['key' => 'vintages_id', 'label' => trans('nksoft::common.vintages'), 'data' => $vintages, 'type' => 'tree'],
                    ['key' => 'regions_id', 'label' => trans('nksoft::common.regions'), 'data' => $regions, 'type' => 'tree'],
                    ['key' => 'brands_id', 'label' => trans('nksoft::common.brands'), 'data' => $brands, 'type' => 'select'],
                    ['key' => 'meta_description', 'label' => trans('nksoft::common.Meta Description'), 'data' => null, 'type' => 'textarea'],
                ],
                'active' => true,
            ],
            [
                'key' => 'inputForm',
                'label' => trans('nksoft::common.Content'),
                'element' => [
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'sku', 'label' => trans('nksoft::common.Sku'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'price', 'label' => trans('nksoft::common.Price'), 'data' => null, 'class' => 'required', 'type' => 'number'],
                    ['key' => 'special_price', 'label' => trans('nksoft::common.Special Price'), 'data' => null, 'type' => 'number'],
                    ['key' => 'alcohol_content', 'label' => trans('nksoft::common.Alcohol Content'), 'data' => null, 'class' => 'required', 'type' => 'number'],
                    ['key' => 'volume', 'label' => trans('nksoft::common.Volume'), 'data' => null, 'class' => 'required', 'type' => 'number'],
                    ['key' => 'smell', 'label' => trans('nksoft::common.Smell'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'text'],
                    ['key' => 'rate', 'label' => trans('nksoft::common.Rate'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'number'],
                    ['key' => 'year_of_manufacture', 'label' => trans('nksoft::common.Year Of Manufacture'), 'data' => null, 'class' => 'col-12 col-lg-4', 'type' => 'text'],
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
            'sku' => 'required',
            'price' => 'required',
            'alcohol_content' => 'required',
            'volume' => 'required',
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
            if (!$data['slug']) {
                $data['slug'] = $data['name'];
            }

            $data['slug'] = Str::slug($data['slug'] . rand(100, strtotime('now')));
            $result = CurrentModel::create($data);
            $this->setCategoryProductsIndex($request, $result);
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
        return view('master::layout');
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
            $result = CurrentModel::select($this->formData)->with(['images', 'categoryProductIndies'])->find($id);
            \array_push($this->formData, 'images');
            \array_push($this->formData, 'categories_id');
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
            foreach ($data as $k => $v) {
                $result->$k = $v;
            }
            if (!$data['slug']) {
                $data['slug'] = Str::slug($data['name'] . rand(100, strtotime('now')), '-');
            }

            $result->save();
            $this->setCategoryProductsIndex($request, $result);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (\Auth::user()->role_id == 1) {
                CurrentModel::find($id)->delete();
                $this->destroyHistories($id, $this->module);
            } else {
                $this->setHistories($id, $this->module);
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
