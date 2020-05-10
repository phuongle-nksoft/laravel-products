<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Orders as CurrentModel;
use Nksoft\Products\Models\Products;
use Nksoft\Products\Models\Promotions;
use Validator;

class OrdersController extends WebController
{
    private $formData = CurrentModel::FIELDS;

    protected $module = 'orders';

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
                ['key' => 'order_id', 'label' => trans('nksoft::products.Order Id')],
                ['key' => 'customers_id', 'label' => trans('nksoft::common.customers')],
                ['key' => 'shippings_id', 'label' => trans('nksoft::common.shippings')],
                ['key' => 'total', 'label' => trans('nksoft::products.Total')],
                ['key' => 'status', 'label' => trans('nksoft::common.Status'), 'data' => config('nksoft.orderStatus')],
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
        return [
            [
                'key' => 'general',
                'label' => trans('nksoft::common.General'),
                'element' => [
                    ['key' => 'promotions', 'label' => trans('nksoft::common.promotions'), 'data' => null, 'readonly' => true, 'type' => 'text'],
                    ['key' => 'discount_amount', 'label' => trans('nksoft::products.Discount Amount'), 'data' => null, 'readonly' => true, 'type' => 'number'],
                    ['key' => 'total', 'label' => trans('nksoft::products.Total'), 'data' => null, 'readonly' => true, 'type' => 'number'],
                    ['key' => 'status', 'label' => trans('nksoft::common.Status'), 'data' => config('nksoft.orderStatus'), 'type' => 'select'],
                    ['key' => 'note', 'label' => trans('nksoft::products.Note'), 'data' => null, 'type' => 'textarea'],
                ],
                'active' => true,
            ],
            [
                'key' => 'customers',
                'label' => trans('nksoft::common.customers'),
                'element' => [
                    ['key' => 'shippings_name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'customer_phone', 'label' => trans('nksoft::common.Phone'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'customer_email', 'label' => trans('nksoft::common.Email'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                ],
            ],
            [
                'key' => 'shippings',
                'label' => trans('nksoft::common.shippings'),
                'element' => [
                    ['key' => 'shippings_name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_phone', 'label' => trans('nksoft::common.Phone'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_address', 'label' => trans('nksoft::settings.Address'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_company', 'label' => trans('nksoft::common.Company'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_provinces', 'label' => trans('nksoft::products.Province'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_districts', 'label' => trans('nksoft::products.District'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'shippings_wards', 'label' => trans('nksoft::products.Wards'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                ],
            ],
            [
                'key' => 'orderDetail',
                'label' => trans('nksoft::products.Order Detail'),
                'element' => [
                    ['key' => 'order_detail_name', 'label' => trans('nksoft::common.Name'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'order_detail_discount', 'label' => trans('nksoft::products.Discount'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'order_detail_special_price', 'label' => trans('nksoft::common.Special Price'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'order_detail_price', 'label' => trans('nksoft::common.Price'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'order_detail_qty', 'label' => trans('nksoft::common.Qty'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
                    ['key' => 'order_detail_subtotal', 'label' => trans('nksoft::products.Subtotal'), 'data' => null, 'defaultValue' => '', 'readonly' => true, 'type' => 'text'],
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
        try {
            $result = CurrentModel::where(['id' => $id])->with(['shipping', 'orderDetails', 'promotion'])->get();
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
     * add item to cart
     */
    public function addCart(Request $request)
    {
        $validator = Validator::make($request->all(), ['qty' => 'required', 'productId' => 'required']);
        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'message' => $validator->errors()]);
        }
        $qty = $request->get('qty');
        $select = ['id', 'name', 'regions_id', 'brands_id', 'is_active', 'price', 'special_price', 'slug', 'price_contact'];
        $with = ['images', 'vintages', 'brands', 'regions', 'professionalsRating'];
        $product = Products::select($select)->where(['id' => $request->get('productId'), 'is_active' => 1])->with($with)->first();
        if (!$product) {
            return \response()->json(['status' => 'error', 'message' => '404']);
        }
        $allCarts = $request->session()->get(config('nksoft.addCart')) ?? [];
        $subtotal = $product->special_price ? $product->special_price * $qty : $product->price * $qty;
        $promotion = session('discount');

        $itemCart = array(
            'rowId' => md5(time()),
            'qty' => $qty,
            'product_id' => $product->id,
            'subtotal' => $subtotal,
            'name' => $product->name,
            'price' => $product->price,
            'special_price' => $product->special_price,
            'images' => $product->images()->first(),
            'professionals_rating' => $product->professionalsRating,
            'vintages' => $product->vintages,
            'regions' => $product->regions,
            'brands' => $product->brands,
            'slug' => $product->slug,
            'discount' => 0,
            'price_contact' => $product->price_contact,
        );
        if (!$allCarts) {
            $allCarts = [];
            array_push($allCarts, $itemCart);
        } else {
            $existsItem = collect($allCarts)->firstWhere('product_id', $product->id);
            if (!$existsItem) {
                array_push($allCarts, $itemCart);
            } else {
                foreach ($allCarts as $key => $item) {
                    if ($item['product_id'] == $product->id) {
                        $allCarts[$key]['qty'] += $qty;
                        $sbt = $product->special_price ? $product->special_price * $allCarts[$key]['qty'] : $product->price * $allCarts[$key]['qty'];
                        $allCarts[$key]['subtotal'] = $sbt;
                    }
                }
            }
        }
        if ($allCarts && $promotion) {
            $allCarts = $this->calcDiscount($allCarts, $promotion);
        }
        session([config('nksoft.addCart') => $allCarts]);
        return $this->responseViewSuccess($allCarts);
    }

    /**
     * get list item cart
     */
    public function getCart()
    {
        $allCarts = request()->session()->get(config('nksoft.addCart')) ?? [];
        return $this->responseViewSuccess($allCarts);
    }

    /**
     * delete item in cart
     */
    public function deteleCart($rowId)
    {
        $allCarts = request()->session()->get(config('nksoft.addCart')) ?? [];
        $allCarts = collect($allCarts)->where('rowId', '<>', $rowId)->toArray();
        request()->session()->put(config('nksoft.addCart'), $allCarts);
        return $this->responseViewSuccess($allCarts);
    }

    /**
     * use discount promotion
     */
    public function discount(Request $request)
    {
        $code = $request->get('code');
        $today = Date('Y-m-d');
        $promotion = Promotions::where(['code' => $code])->whereRaw('(expice_date >= ? or expice_date is null) and (start_date <= ? or start_date is null)', [$today, $today])->first();
        if (!$promotion) {
            return $this->responseError(['Mã code không hợp lệ']);
        }
        $cart = $this->calcDiscount(session(config('nksoft.addCart')), $promotion);
        session([config('nksoft.addCart') => $cart]);
        session(['discount' => $promotion]);
        return $this->responseViewSuccess(['discount' => $promotion, 'cart' => $cart], ['Mã code đã được áp dụng.']);
    }

    /**
     * delete promotion
     */
    public function deleteCode()
    {
        session()->forget('discount');
        $cart = $this->calcDiscount(session(config('nksoft.addCart')));
        session([config('nksoft.addCart') => $cart]);
        return $this->responseViewSuccess(['discount' => null, 'cart' => $cart], ['Mã đã xóa mã giảm giá.']);
    }

    /**
     * calculate discount
     */
    private function calcDiscount($cart, $promotion = null)
    {
        $productIds = [];
        if ($promotion) {
            $productIds = json_decode($promotion->product_ids) ?? [];
        }
        if ($cart) {
            foreach ($cart as $key => $item) {
                $price = $item['special_price'] ?? $item['price'];
                $discount = 0;
                if (!is_null($promotion) && count($productIds) > 0 && in_array($item['product_id'], $productIds)) {
                    $discount = $promotion->simple_action == 1 ? ($promotion->discount_amount / 100) * $price : $promotion->discount_amount;
                }
                if ($promotion && $promotion->all_products) {
                    $discount = $promotion->simple_action == 1 ? ($promotion->discount_amount / 100) * $price : $promotion->discount_amount;
                }
                $cart[$key]['discount'] = $discount * $item['qty'];
            }
        }
        return $cart;
    }

    /**
     * get discount
     */
    public function getDiscount()
    {
        try {
            $response = array(
                'listDiscount' => Promotions::where(['is_active' => 1, 'coupon_type' => 1])->whereRaw('(expice_date > ? or expice_date is null)', date('Y-m-d'))->get(),
                'discountUsed' => session('discount'),
            );
            return $this->responseViewSuccess($response);
        } catch (\Exception $e) {
            return $this->responseError([$e->getMessage()]);
        }
    }
}
