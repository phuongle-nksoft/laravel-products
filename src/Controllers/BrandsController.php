<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Brands as CurrentModel;
use Nksoft\Products\Models\CategoryProductsIndex;
use Nksoft\Products\Models\Products;
use Nksoft\Products\Models\ProfessionalRatings;
use Nksoft\Products\Models\Regions;
use Nksoft\Products\Models\VintagesProductIndex;

class BrandsController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'brands';

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
                'rows' => $results->with(['histories'])->orderBy('created_at', 'desc')->get(),
                'columns' => $columns,
                'module' => $this->module,
                'listDelete' => CurrentModel::whereIn('id', $listDelete)->get(),
                'showSearch' => true,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e);
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
            \array_push($this->formData, 'banner');
            $response = [
                'formElement' => $this->formElement(),
                'result' => null,
                'formData' => $this->formData,
                'module' => $this->module,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e);
        }
    }

    private function formElement($result = null)
    {
        $status = $this->status();
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $status, 'type' => 'select'],
                    ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => $this->getTypeProducts(), 'type' => 'select'],
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'type' => 'editor'],
                    ['key' => 'order_by', 'label' => trans('nksoft::common.Order By'), 'data' => null, 'type' => 'number'],
                    ['key' => 'slug', 'label' => trans('nksoft::common.Slug'), 'data' => null, 'type' => 'text'],
                    ['key' => 'video_id', 'label' => 'Video', 'data' => null, 'type' => 'text'],
                    ['key' => 'banner', 'label' => trans('nksoft::common.Banner'), 'data' => null, 'type' => 'image'],
                    ['key' => 'images', 'label' => trans('nksoft::common.Images'), 'data' => null, 'type' => 'image'],
                ],
                'active' => true,
                'selected' => $result && $result->parent_id == 0,
            ],
            [
                'key' => 'inputForm',
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
        $rules = [
            'name' => 'required',
            'images[]' => 'file',
        ];

        return $rules;
    }

    private function message()
    {
        return [
            'name.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Name')]),
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
                if (!\in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }
            if ($request->get('duplicate')) {
                $data['slug'] = null;
            }
            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
            $this->media($request, $result);
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
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
            $result = CurrentModel::select($this->formData)->with(['images'])->where(['is_active' => 1, 'id' => $id])->first();
            if (!$result) {
                return $this->responseError('404');
            }
            $products = Products::where(['brands_id' => $id, 'is_active' => 1])
                ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating'])->orderBy('price', 'asc');
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
            $listFilter = $this->listFilter($result->type, $products);
            if ($result->type != 1) {
                $listFilter = array_filter($listFilter, function ($item) {
                    return !in_array($item['type'], ['p']);
                });
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
                'filter' => $listFilter,
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
            $result = CurrentModel::select($this->formData)->with(['images'])->find($id);
            \array_push($this->formData, 'images');
            \array_push($this->formData, 'banner');
            $response = [
                'formElement' => $this->formElement($result),
                'result' => $result,
                'formData' => $this->formData,
                'module' => $this->module,
            ];
            return $this->responseSuccess($response);
        } catch (\Execption $e) {
            return $this->responseError($e);
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
                if (!\in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }
            foreach ($data as $k => $v) {
                $result->$k = $v;
            }
            $data['slug'] = $this->getSlug($data);
            $result->save();
            $this->setUrlRedirects($result);
            $this->media($request, $result);
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }
}
