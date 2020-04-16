<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Customers as CurrentModel;
use \Arr;
use \Auth;

class CustomersController extends WebController
{
    private $formData = ['id', 'is_active', 'name', 'email', 'password'];

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
            'name' => 'required',
            'email' => 'required|email',
            'images[]' => 'file',
        ];
        if ($id == 0) {
            $rules['password'] = 'required|min:6';
        }

        return $rules;
    }

    private function message()
    {
        return [
            'name.required' => __('nksoft::message.Field is require!', ['Field' => trans('nksoft::Users.Username')]),
            'email.required' => __('nksoft::message.Field is require!', ['Field' => 'Email']),
            'email.email' => __('nksoft::message.Email is incorrect!'),
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
            return \response()->json(['status' => 'error', 'message' => $validator->errors()]);
        }
        try {
            $data = [];
            foreach ($this->formData as $item) {
                if ($item != 'images') {
                    $data[$item] = $request->get($item);
                }
            }
            $data['password'] = \Hash::make($data['password']);
            $user = CurrentModel::create($data);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $user->id, $this->module);
            }
            $response = [
                'result' => $user,
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
            $data = [];
            foreach ($this->formData as $item) {
                if ($item != 'images' && $item != 'id') {
                    $data[$item] = $request->get($item);
                }
            }
            if ($data['password'] != 'undefined') {
                $data['password'] = \Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            foreach ($data as $k => $v) {
                $user->$k = $v;
            }
            $user->save();
            // $user = CurrentModel::save(['id' => $id], $data);
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $this->setMedia($images, $user->id, $this->module);
            }
            $response = [
                'result' => $user,
            ];
            return $this->responseSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError($e);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|max:32',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors([trans('nksoft::login.Email or password is incorrect!')], 'login');
        }

        $credentials = $request->only('email', 'password', 'active');
        if (Auth::attempt($credentials)) {
            return redirect()->to('admin');
        }
        return redirect()->back()->withErrors([trans('nksoft::login.Email or password is incorrect!')], 'login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->to('login');
    }

    public function loginSerices($service)
    {
        dd($service);
    }
    public function callback($service)
    {
        dd($service);
    }
}
