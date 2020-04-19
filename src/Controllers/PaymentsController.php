<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Models\Customers;
use Nksoft\Products\Models\OrderDetails;
use Nksoft\Products\Models\Orders;
use Nksoft\Products\Models\Payments as CurrentModel;
use Nksoft\Products\Models\Products;

class PaymentsController extends WebController
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

    public function vnpayUrl($order, $request)
    {
        $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = env('APP_URL') . "payments/vnpay/callback";
        $vnp_TmnCode = env('VNP_TMNCODE'); //Mã website tại VNPAY
        $vnp_HashSecret = env('VNP_HASHSECRET'); //Chuỗi bí mật

        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = 'Thanh toan website';
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total * 100;
        $vnp_Locale = config('app.locale');
        $vnp_BankCode = $request->get('bankActive');
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
            $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
        }
        return $vnp_Url;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), ['shippings_id' => 'required'], ['shippings_id' => 'Bạn chưa chọn địa chỉ giao hàng']);
        if ($validator->fails()) {
            return $this->responseError($validator->errors());
        }
        $cart = session(config('nksoft.addCart'));
        if (!$cart) {
            return $this->responseError(['Không tìm thấy sản phẩm']);
        }
        $user = session('user');
        if (!$user) {
            return $this->responseError(['Vui lòng đăng nhập']);
        }
        $promotion = $request->get('discount');
        $total = collect($cart)->sum('subtotal');
        if ($promotion) {
            $discountAmount = $promotion['simple_action'] == 1 ? $promotion['discount_amount'] / 100 * $total : $promotion['discount_amount'];
            $total = $total - $discountAmount;
        }
        $orderData = [
            'shippings_id' => $request->get('shippings_id'),
            'customers_id' => $user->id,
            'status' => 1,
            'discount_code' => $promotion['code'] ?? '',
            'promotion_id' => $promotion['id'] ?? 0,
            'discount_amount' => $promotion['discount_amount'] ?? 0.00,
            'total' => collect($cart)->sum('subtotal'),
        ];
        $order = Orders::create($orderData);
        if ($order) {
            $dataDetails = [];
            foreach ($cart as $item) {
                $dataDetails[] = [
                    'orders_id' => $order->id,
                    'products_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'special_price' => $item['special_price'],

                ];
            }
            OrderDetails::insert($dataDetails);
            $vnp_Url = $this->vnpayUrl($order, $request);
            return $this->responseViewSuccess(['url' => $vnp_Url]);
        }
        return $this->responseError(['Đơn hàng chưa được tạo']);
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

    public function callback(Request $request, $service)
    {
        $orderId = $request->get('vnp_TxnRef');
        $responseCode = $request->get('vnp_ResponseCode');
        $data = array(
            'Amount' => $request->get('vnp_Amount'),
            'BankCode' => $request->get('vnp_BankCode'),
            'BankTranNo' => $request->get('vnp_BankTranNo'),
            'CardType' => $request->get('vnp_CardType'),
            'OrderInfo' => $request->get('vnp_OrderInfo'),
            'PayDate' => $request->get('vnp_PayDate'),
            'ResponseCode' => $request->get('vnp_ResponseCode'),
            'TmnCode' => $request->get('vnp_TmnCode'),
            'TransactionNo' => $request->get('vnp_TransactionNo'),
            'TxnRef' => $request->get('vnp_TxnRef'),
            'orders_id' => $orderId,
            'SecureHashType' => $request->get('vnp_SecureHashType'),
            'SecureHash' => $request->get('vnp_SecureHash'),
        );
        if ($responseCode == '00') {
            CurrentModel::create($data);
            $order = Orders::where(['id' => $orderId])->update(['status' => 2]);
            $customer = Customers::where(['id' => $order->customers_id])->with(['shipping', 'orders'])->first();
            session()->put('user', $customer);
            session()->forget(config('nksoft.addCart'));
            return redirect('success');
        } else {
            Orders::where(['id' => $orderId])->forceDelete();
            return redirect('fails');
        }
    }
}
