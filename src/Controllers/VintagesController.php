<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\CategoryProductsIndex;
use Nksoft\Products\Models\Products;
use Nksoft\Products\Models\ProfessionalRatings;
use Nksoft\Products\Models\Regions;
use Nksoft\Products\Models\Vintages as CurrentModel;
use Nksoft\Products\Models\VintagesProductIndex;

class VintagesController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'vintages';

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
                ['key' => 'order_by', 'label' => trans('nksoft::common.Order By')],
                ['key' => 'id', 'label' => 'Id', 'type' => 'hidden'],
                ['key' => 'name', 'label' => trans('nksoft::common.Name')],
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => $this->getTypeProducts(), 'type' => 'select'],
            ];
            $select = Arr::pluck($columns, 'key');
            $results = CurrentModel::select($select);
            $q = request()->get('q');
            if ($q) {
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
        $categories = [
            [
                'name' => trans('nksoft::common.vintages'),
                'id' => 0,
                'icon' => 'fas fa-folder',
                'state' => [
                    'opened' => true,
                    'selected' => $result && $result->parent_id == 0,
                ],
                'children' => CurrentModel::GetListCategories(array('parent_id' => 0), $result),
            ],
        ];
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                    ['key' => 'type', 'label' => trans('nksoft::products.Type'), 'data' => $this->getTypeProducts(), 'type' => 'select'],
                    ['key' => 'parent_id', 'label' => trans('nksoft::common.vintages'), 'data' => $categories, 'type' => 'select'],
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'type' => 'editor'],
                    ['key' => 'order_by', 'label' => trans('nksoft::common.Order By'), 'data' => null, 'type' => 'number'],
                    ['key' => 'slug', 'label' => trans('nksoft::common.Slug'), 'data' => null, 'type' => 'text'],
                    ['key' => 'video_id', 'label' => 'Video', 'data' => null, 'type' => 'text'],
                    ['key' => 'banner', 'label' => trans('nksoft::common.Banner'), 'data' => null, 'type' => 'image'],
                    ['key' => 'images', 'label' => trans('nksoft::common.Images'), 'data' => null, 'type' => 'image'],
                ],
                'active' => true,
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
                if (!in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }
            if (!$data['parent_id']) {
                $data['parent_id'] = 0;
            }

            if ($request->get('duplicate')) {
                $data['slug'] = null;
            }
            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
            if ($request->hasFile('banner')) {
                $images = $request->file('banner');
                $this->setMedia($images, $result->id, $this->module, 2);
            }
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
            $rootItem = in_array($id, [37]);
            $where = ['is_active' => 1, 'id' => $id];
            if ($rootItem) {
                $where = ['id' => $id];
            }

            $result = CurrentModel::select($this->formData)->with(['images'])->where($where)->first();
            if (!$result) {
                return $this->responseError('404');
            }
            if ($rootItem) {
                $listIds = CurrentModel::GetListIds(['parent_id' => 0, 'type' => $result->type]);
            } else {
                $listIds = CurrentModel::GetListIds(['id' => $id]);
            }

            if (!$result) {
                return $this->responseError('404');
            }
            $products = Products::where('vintages_banner_id', $id);
            if ($products->count() < 1) {
                $products = Products::whereIn('id', function ($query) use ($listIds) {
                    $query->from(with(new VintagesProductIndex())->getTable())->select(['products_id'])->whereIn('vintages_id', $listIds)->groupBy('products_id')->pluck('products_id');
                });
            }
            $products = $products->where(['is_active' => 1])->orderBy('order_by', 'asc')->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
            $allRequest = request()->all();
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
            if ($rootItem && isset($allRequest['r'])) {
                $regionId = $allRequest['r'];
                $products = $products->where(['regions_id' => $regionId]);
            }
            if (isset($allRequest['vg'])) {
                $vingateId = $allRequest['vg'];
                $products = $products->whereIn('id', function ($query) use ($vingateId) {
                    $query->from(with(new VintagesProductIndex())->getTable())->select(['products_id'])->where('vintages_id', $vingateId)->pluck('products_id');
                });
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
            $allowId = [37];
            $listFilter = $this->listFilter($result->type, $products, !in_array($id, $allowId) ? 'vg' : '');
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
                if (!in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }
            if (!$data['parent_id']) {
                $data['parent_id'] = 0;
            }
            $data['slug'] = $this->getSlug($data);
            foreach ($data as $k => $v) {
                $result->$k = $v;
            }

            $result->save();
            $this->setUrlRedirects($result);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $result->id, $this->module);
            }
            if ($request->hasFile('banner')) {
                $images = $request->file('banner');
                $this->setMedia($images, $result->id, $this->module, 2);
            }
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }
}
