<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Customers;
use Nksoft\Products\Models\Provinces;
use Nksoft\Products\Models\Shipping as CurrentModel;

class ShippingsController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'shippings';

    protected $model = CurrentModel::class;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    private function rules()
    {
        $rules = [
            'address' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'provinces_id' => 'required',
        ];
        return $rules;
    }

    private function message()
    {
        return [
            'address.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::settings.Address')]),
            'name.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::common.Name')]),
            'phone.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::users.Phone')]),
            'provinces_id.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::products.Province')]),
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
                if ($item != 'images') {
                    $data[$item] = $request->get($item);
                }
            }
            if ($data['is_default']) {
                CurrentModel::where(['customers_id' => $data['customers_id']])->update(['is_default' => 0, 'last_shipping' => 0]);
            }
            $shipping = CurrentModel::create($data);
            $customer = Customers::where(['id' => $data['customers_id']])->with('shipping')->first();
            session()->put('user', $customer);
            $response = [
                'user' => $customer,
                'shipping' => $shipping,
            ];
            return $this->responseViewSuccess($response, [trans('nksoft::message.Success')]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $validator = Validator($request->all(), $this->rules(), $this->message());
        if ($validator->fails()) {
            return $this->responseError($validator->errors());
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if ($item != 'images') {
                    $data[$item] = $request->get($item);
                }
            }
            if ($data['is_default']) {
                CurrentModel::where(['customers_id' => $data['customers_id']])->update(['is_default' => 0, 'last_shipping' => 0]);
            }
            $shipping = CurrentModel::updateOrCreate(['id' => $id], $data);
            $customer = Customers::where(['id' => $data['customers_id']])->with('shipping')->first();
            session()->put('user', $customer);
            $response = [
                'user' => $customer,
                'shipping' => $shipping,
            ];
            return $this->responseViewSuccess($response, [trans('nksoft::message.Success')]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
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
            $shipping = CurrentModel::find($id);
            $customerId = $shipping->customer->id;
            $shipping->forceDelete();
            $customer = Customers::where(['id' => $customerId])->with('shipping')->first();
            session()->put('user', $customer);
            $response = [
                'user' => $customer,
            ];
            return $this->responseViewSuccess($response, [trans('nksoft::message.Success')]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getProvinces()
    {

        try {
            $provinces = Provinces::with(['districts'])->get();
            $response = [
                'provinces' => $provinces,
            ];
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }
}
