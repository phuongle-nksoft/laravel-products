<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Products;
use Nksoft\Products\Models\Promotions;
use Validator;

class OrdersController extends WebController
{
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

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

    public function getCart()
    {
        $allCarts = request()->session()->get(config('nksoft.addCart')) ?? [];
        return $this->responseViewSuccess($allCarts);
    }

    public function deteleCart($rowId)
    {
        $allCarts = request()->session()->get(config('nksoft.addCart')) ?? [];
        $allCarts = collect($allCarts)->where('rowId', '<>', $rowId)->toArray();
        request()->session()->put(config('nksoft.addCart'), $allCarts);
        return $this->responseViewSuccess($allCarts);
    }

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

    private function calcDiscount($cart, $promotion)
    {
        $productIds = json_decode($promotion->product_ids);
        if (!$productIds) {
            $productIds = [];
        }
        if ($cart) {
            foreach ($cart as $key => $item) {
                $price = $item['special_price'] ?? $item['price'];
                $discount = 0;
                if (count($productIds) > 0 && in_array($item['product_id'], $productIds)) {
                    $discount = $promotion->simple_action == 1 ? ($promotion->discount_amount / 100) * $price : $promotion->discount_amount;
                }
                if ($promotion->all_products) {
                    $discount = $promotion->simple_action == 1 ? ($promotion->discount_amount / 100) * $price : $promotion->discount_amount;
                }
                $cart[$key]['discount'] = $discount * $item['qty'];
            }
        }
        return $cart;
    }
}
