<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Products;
use Nksoft\Products\Models\Promotions as CurrentModel;
use Validator;

class PromotionsController extends WebController
{
    private $formData = ['id', 'name', 'code', 'coupon_type', 'discount_amount', 'expice_date', 'start_date', 'is_active', 'discount_qty', 'product_ids', 'description', 'simple_action'];

    protected $module = 'promotions';

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
                ['key' => 'discount_amount', 'label' => trans('nksoft::products.Discount Amount')],
                ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status()],
            ];
            $select = collect($columns)->pluck('key')->toArray();
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
        $simpleAction = config('nksoft.simpleAction');
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'description', 'label' => trans('nksoft::common.Description'), 'data' => null, 'type' => 'editor'],
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $status, 'type' => 'select'],
                ],
                'active' => true,
            ],
            [
                'key' => 'inputForm',
                'label' => trans('nksoft::common.Content'),
                'element' => [
                    ['key' => 'coupon_type', 'label' => trans('nksoft::products.Coupon Type'), 'data' => $this->status(), 'class' => 'col-md-3', 'type' => 'select'],
                    ['key' => 'code', 'label' => trans('nksoft::products.Code'), 'data' => null, 'type' => 'text'],
                    ['key' => 'simple_action', 'label' => trans('nksoft::products.Simple Action'), 'data' => $simpleAction, 'type' => 'select'],
                    ['key' => 'discount_amount', 'label' => trans('nksoft::products.Discount Amount'), 'data' => null, 'type' => 'number'],
                    ['key' => 'start_date', 'label' => trans('nksoft::products.From Date'), 'data' => null, 'type' => 'date'],
                    ['key' => 'expice_date', 'label' => trans('nksoft::products.To Date'), 'data' => null, 'type' => 'date'],
                    ['key' => 'discount_qty', 'label' => trans('nksoft::products.Discount Qty'), 'data' => null, 'type' => 'number'],
                ],
            ],
        ];
    }

    private function rules()
    {
        $rules = [
            'name' => 'required',
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
            if (!$data['coupon_type']) {
                $data['coupon_type'] = 0;
            }
            if ($this->validateDate($data['start_date'])) {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            }
            if ($this->validateDate($data['expice_date'])) {
                $data['expice_date'] = date('Y-m-d', strtotime($data['expice_date']));
            }
            $result = CurrentModel::create($data);
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
            $result->start_date = $result->start_date ? date('d/m/Y', \strtotime($result->start_date)) : '';
            $result->expice_date = $result->expice_date ? date('d/m/Y', \strtotime($result->expice_date)) : '';
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
            if (!$data['coupon_type']) {
                $data['coupon_type'] = 0;
            }
            if ($this->validateDate($data['start_date'])) {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            }
            if ($this->validateDate($data['expice_date'])) {
                $data['expice_date'] = date('Y-m-d', strtotime($data['expice_date']));
            }
            foreach ($data as $k => $v) {
                $result->$k = $v;
            }
            $result->save();
            $response = [
                'result' => $result,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }
}
