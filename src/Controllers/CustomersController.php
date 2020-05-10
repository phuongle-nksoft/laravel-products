<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Customers as CurrentModel;
use Nksoft\Products\Models\Products;
use \Arr;

class CustomersController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'customers';

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
                ['key' => 'email', 'label' => trans('nksoft::users.Email')],
            ];
            $select = Arr::pluck($columns, 'key');
            $users = CurrentModel::select($select)->with(['histories'])->paginate();
            $listDelete = $this->getHistories($this->module)->pluck('parent_id');
            $response = [
                'rows' => $users,
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
            \array_push($this->formData, 'images');
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

    private function formElement()
    {
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'is_active', 'label' => trans('nksoft::common.Status'), 'data' => $this->status(), 'type' => 'select'],
                ],
                'active' => true,
            ],
            [
                'key' => 'inputForm',
                'label' => trans('nksoft::common.Content'),
                'element' => [
                    ['key' => 'name', 'label' => trans('nksoft::users.Username'), 'data' => null, 'class' => 'required', 'type' => 'text'],
                    ['key' => 'email', 'label' => trans('nksoft::users.Email'), 'data' => null, 'class' => 'required', 'type' => 'email'],
                    ['key' => 'password', 'label' => trans('nksoft::users.Password'), 'data' => null, 'class' => 'required', 'type' => 'password'],
                    ['key' => 'images', 'label' => trans('nksoft::users.Avatar'), 'type' => 'image'],
                ],
            ],
        ];
    }

    private function rules($id = 0)
    {
        $rules = [
            'email' => 'required|email',
            'images[]' => 'file',
        ];
        if ($id == 0) {
            $rules['email'] = 'email | unique:customers';
            $rules['password'] = 'required|min:6';
            $rules['phone'] = 'required';
            $rules['name'] = 'required';
        }

        return $rules;
    }

    private function message()
    {
        return [
            'email.required' => __('nksoft::message.Field is require!', ['Field' => 'Email']),
            'email.email' => __('nksoft::message.Email is incorrect!'),
            'email.unique' => __('nksoft::message.Field is duplicate', ['Field' => 'Email']),
            'password.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::login.Password')]),
            'password.min' => __('nksoft::message.Field more than number letter!', ['Field' => trans('nksoft::login.Password'), 'number' => 6]),
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
            $data['password'] = \Hash::make($data['password']);
            $data['is_active'] = 1;
            $user = CurrentModel::create($data);
            session()->put('user', $user);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $user->id, $this->module);
            }
            $response = [
                'user' => $user,
            ];
            return $this->responseViewSuccess($response, [trans('nksoft::message.Success')]);
        } catch (\Exception $e) {
            return $this->responseError([__('nksoft::message.Field is duplicate', ['Field' => 'Email']), $e->getMessage()]);
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
            $result = CurrentModel::select($this->formData)->with(['images'])->find($id);
            \array_push($this->formData, 'images');
            $response = [
                'formElement' => $this->formElement(),
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
        $user = CurrentModel::find($id);
        if ($user == null) {
            return $this->responseError();
        }
        $validator = Validator($request->all(), $this->rules($id), $this->message());
        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'message' => $validator->errors()]);
        }
        try {
            $isApi = $request->get('isApi');
            $data = [];
            foreach ($this->formData as $item) {
                if ($item != 'images' && $item != 'id') {
                    $data[$item] = $request->get($item);
                }
            }
            if ($data['password'] && $data['password'] != 'undefined') {
                $data['password'] = \Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            unset($data['isApi']);
            foreach ($data as $k => $v) {
                $user->$k = $v;
            }
            $user->save();
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $user->id, $this->module);
            }
            $response = [
                'result' => $user,
            ];
            if ($isApi) {
                session()->put('user', $user);
            }

            return $isApi ? $this->responseViewSuccess(['user' => $user], [trans('nksoft::message.Success')]) : $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator($request->all(), $this->rules(true), $this->message());
        if ($validator->fails()) {
            return $this->responseError($validator->errors());
        }

        $credentials = $request->only('email', 'password');
        $customer = CurrentModel::select(['id', 'name', 'email', 'password', 'phone'])->where(['email' => $credentials['email']])->with(['shipping', 'orders', 'wishlists'])->first();
        if (!$customer) {
            return $this->responseError([trans('nksoft::message.Account is incorrect!')]);
        }
        if (\Hash::check($credentials['password'], $customer->password)) {
            session()->put('user', $customer);
            return $this->responseViewSuccess(['user' => $customer], []);
        }
        session()->forget('user');
        return $this->responseError([trans('nksoft::login.Email or password is incorrect!')]);
    }

    public function logout()
    {
        session()->forget('user');
        return $this->responseViewSuccess();
    }

    public function loginSerices($service)
    {
        session(['urlLogin' => request()->headers->get('referer')]);
        return Socialite::driver($service)->redirect();
    }
    public function callback($service)
    {
        $user = Socialite::driver($service)->user();
        $email = $user->email;
        $name = $user->name;
        $customer = CurrentModel::where(['email' => $user->getEmail()])->with(['shipping', 'wishlists', 'orders'])->first();
        if (!$customer) {
            $customer = CurrentModel::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('social'),
                'is_active' => 1,
            ]);
        }
        session()->put('user', $customer);
        return redirect()->to(session('urlLogin'));
    }

    /**
     * Get list my wine
     */
    public function myWine()
    {
        try {
            $user = session('user');
            if (!$user) {
                return $this->responseError(['404']);
            }

            $customerId = $user->id;
            $customer = CurrentModel::find($customerId);
            if (!$customer) {
                return $this->responseError('404');
            }
            $wishlistIds = $customer->wishlists()->pluck('products_id')->toArray();
            $products = Products::whereIn('id', $wishlistIds);
            $key = request()->get('key');
            $sort = request()->get('sort');
            if ($key && $sort) {
                $products = $products->orderBy($key, $sort);
            }

            return $this->responseViewSuccess(['wishlist' => $products->with(['images', 'categoryProductIndies', 'vintages', 'brands', 'regions', 'professionalsRating'])->paginate()]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    /**
     * Get list histories user buy products
     */
    public function histories()
    {
        try {
            $user = session('user');
            if (!$user) {
                return $this->responseError(['404']);
            }

            $customerId = $user->id;
            $customer = CurrentModel::find($customerId);
            if (!$customer) {
                return $this->responseError('404');
            }
            $orders = $customer->orders()->with(['orderDetails'])->orderBy('created_at', 'desc')->paginate();
            return $this->responseViewSuccess(['orders' => $orders, 'status' => config('nksoft.orderStatus')]);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }

    public function getUser()
    {
        $user = session('user');
        if (!$user) {
            return $this->responseError(['404']);
        }
        return $this->responseViewSuccess(['user' => $user]);
    }
}
