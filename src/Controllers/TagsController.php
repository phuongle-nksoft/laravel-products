<?php

namespace Nksoft\Products\Controllers;

use Arr;
use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Tags as CurrentModel;

class TagsController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'tags';

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
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
            ];
            $select = Arr::pluck($columns, 'key');
            $results = CurrentModel::select($select)->with(['histories'])->orderBy('created_at', 'desc')->get();
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $results,
                'columns' => $columns,
                'module' => $this->module,
                'listDelete' => CurrentModel::whereIn('id', $listDelete)->get(),
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
            return $this->responseSuccess($response, false);
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
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                ],
                'active' => true,
                'selected' => $result && $result->parent_id == 0,
            ],
        ];
    }

    private function rules()
    {
        $rules = [
            'name' => 'required | unique:tags',
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
            return $this->responseError($validator->errors());
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if (!\in_array($item, $this->excludeCol)) {
                    $data[$item] = $request->get($item);
                }
            }
            $data['slug'] = $this->getSlug($data);
            $result = CurrentModel::create($data);
            $this->setUrlRedirects($result);
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
            $result = CurrentModel::select(['description', 'name', 'meta_description', 'id'])->with(['images'])->where(['is_active' => 1, 'id' => $id])->first();
            if (!$result) {
                return $this->responseError('404');
            }
            $products = Products::where(['brands_id' => $id, 'is_active' => 1])
                ->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating']);
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
            $result = CurrentModel::select($this->formData)->find($id);
            $response = [
                'formElement' => $this->formElement($result),
                'result' => $result,
                'formData' => $this->formData,
                'module' => $this->module,
            ];
            return $this->responseSuccess($response, false);
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
            return $this->responseError([$validator->errors()]);
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
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }
}
