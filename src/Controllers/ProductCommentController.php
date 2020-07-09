<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Customers;
use Nksoft\Products\Models\ProductComments as CurrentModel;
use Nksoft\Products\Models\Products;

class ProductCommentController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'comments';

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
                ['key' => 'description', 'label' => trans('nksoft::common.Description')],
                ['key' => 'products_id', 'label' => trans('nksoft::common.products'), 'relationship' => 'product'],
                ['key' => 'status', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
            ];
            $select = Arr::pluck($columns, 'key');
            $q = request()->get('q');
            $results = CurrentModel::where(['parent_id' => 0])->select($select);
            if ($q) {
                $results = $results->where('name', 'like', '%' . $q . '%');
            }
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $results->with(['histories', 'product'])->orderBy('created_at', 'desc')->get(),
                'columns' => $columns,
                'module' => $this->module,
                'listDelete' => CurrentModel::whereIn('id', $listDelete)->get(),
                'showSearch' => true,
                'disableNew' => true,
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
        $products = Products::select(['id', 'name'])->get();
        $customers = Customers::select(['id', 'name'])->get();
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'status', 'label' => trans('nksoft::common.Status'), 'data' => $status, 'type' => 'select'],
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'disabled', 'type' => 'text'],
                    ['key' => 'description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'type' => 'editor', 'class' => 'disabled'],
                    ['key' => 'products_id', 'label' => trans('nksoft::common.products'), 'data' => $products, 'type' => 'select', 'class' => 'disabled'],
                    ['key' => 'customers_id', 'label' => trans('nksoft::common.customers'), 'data' => $customers, 'type' => 'select', 'class' => 'disabled'],
                    ['key' => 'name_reply', 'label' => 'Người trả lời', 'data' => null, 'type' => 'text'],
                    ['key' => 'reply', 'label' => 'Trả lời', 'data' => null, 'type' => 'editor'],
                ],
                'active' => true,
                'selected' => $result && $result->parent_id == 0,
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
            $result = CurrentModel::select(['description', 'name', 'type', 'meta_description', 'type', 'id'])->with(['images'])->where(['is_active' => 1, 'id' => $id])->first();
            if (!$result) {
                return $this->responseError('404');
            }
            $products = Products::where(['brands_id' => $id, 'is_active' => 1])
                ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating'])->orderBy('price', 'asc');
            $image = $result->images()->first();
            $im = $image ? 'storage/' . $image->image : 'wine/images/share/logo.svg';
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
                'seo' => [
                    'title' => $result->name,
                    'ogDescription' => $result->meta_description,
                    'ogUrl' => url($result->slug),
                    'ogImage' => url($im),
                    'ogSiteName' => $result->name,
                ],
                'filter' => $this->listFilter($result->type),
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
            array_push($this->formData, 'reply');
            array_push($this->formData, 'name_reply');
            $children = $result->children()->first();
            $result->reply = $children ? $children->description : '';
            $result->name_reply = $children ? $children->name : '';
            $response = [
                'formElement' => $this->formElement($result),
                'result' => $result,
                'formData' => $this->formData,
                'module' => $this->module,
                'disableNew' => true,
                'disableDuplicate' => true,
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
        array_push($this->formData, 'reply');
        array_push($this->formData, 'name_reply');
        $result = CurrentModel::find($id);
        if ($result == null) {
            return $this->responseError();
        }
        $customer = Customers::where(['email' => 'admin@ruounhapkhau.com'])->first();
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!\in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }

            $children = $result->children()->first();
            if ($children) {
                $children->description = $data['reply'];
                $children->name = $data['name_reply'];
                $children->save();
            } else {
                CurrentModel::create(['products_id' => $result->products_id, 'customers_id' => $customer->id, 'description' => $data['reply'], 'status' => 1, 'name' => $data['name_reply'], 'parent_id' => $result->id]);
            }
            $result->status = $data['status'] ? $data['status'] : 0;
            $result->description = $data['description'];
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
