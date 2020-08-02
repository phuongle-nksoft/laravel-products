<?php

namespace Nksoft\Products\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Nksoft\Master\Controllers\WebController;
use Nksoft\Products\Mail\OrderMail;
use Nksoft\Products\Models\Customers;
use Nksoft\Products\Models\Notifications;
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
        if (\App::environment('local')) {
            $vnp_Url = "http://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        } else {
            $vnp_Url = "https://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        }

        $vnp_Returnurl = env('APP_URL') . "payments/vnpay/callback";
        $vnp_TmnCode = env('VNP_TMNCODE'); //Mã website tại VNPAY
        $vnp_HashSecret = env('VNP_HASHSECRET'); //Chuỗi bí mật

        $vnp_TxnRef = $order['order_id'];
        $vnp_OrderInfo = 'Thanh toan website';
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order['total'] * 100;
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
        $discount = collect($cart)->sum('discount');
        $price_contact = collect($cart)->firstWhere('price_contact', 1);
        if ($promotion) {
            $total = $total - $discount;
        }
        $provinceId = $request->get('provinces_id');
        $area = $request->get('area');
        $orderData = [
            'shippings_id' => $request->get('shippings_id'),
            'customers_id' => $user->id,
            'status' => 1,
            'discount_code' => $promotion['code'] ?? '',
            'promotion_id' => $promotion['id'] ?? 0,
            'discount_amount' => $promotion['discount_amount'] ?? 0.00,
            'total' => $total,
            'order_id' => bin2hex(random_bytes(4)),
            'price_contact' => $price_contact ? 1 : 0,
            'area' => $area,
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
                    'discount' => $item['discount'],
                    'special_price' => $item['special_price'],
                ];
            }
            OrderDetails::insert($dataDetails);
        }
        $order = Orders::where(['id' => $order->id])->with(['shipping', 'customer'])->first();
        session(['order' => $order]);
        if ($price_contact || !in_array($provinceId, [1, 50, 32])) {
            $this->resetSession($order);
            $emailSend = [
                3 => 'saleMB@ruounhapkhau.com',
                2 => 'saleMT@ruounhapkhau.com',
                1 => 'saleMN@ruounhapkhau.com',
            ];

            Notifications::createItem(1, $user->id);
            Mail::to($emailSend[$area])->cc('leduyphuong64@gmail.com')->send(new OrderMail($order));
            return $this->responseViewSuccess(['url' => url('dat-hang-thanh-cong')]);
        } else {
            session(['orderPayment' => $orderData]);
            $vnp_Url = $this->vnpayUrl($orderData, $request);
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

    public function checkVNPayCallback(Request $request, $ipn = false)
    {
        \Log::info($request->fullUrl());
        $data = $request->all();
        $inputData = array();
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        $vnp_HashSecret = env('VNP_HASHSECRET');
        $secureHash = hash('sha256', $vnp_HashSecret . $hashData);
        $orderId = $inputData['vnp_TxnRef'];
        $order = Orders::where(['order_id' => $orderId])->first();
        $total = $data['vnp_Amount'] / 100;
        try {
            $vnpayResponse = array();
            if ($secureHash == $vnp_SecureHash) {
                $responseCode = $request->get('vnp_ResponseCode');
                if (!$order) {
                    $vnpayResponse['RspCode'] = '01';
                    $vnpayResponse['Message'] = 'Order not found';
                } else if ($order->total != $total) {
                    $vnpayResponse['RspCode'] = '04';
                    $vnpayResponse['Message'] = 'Invalid amount';
                } else if ($order->status > 1) {
                    $vnpayResponse['RspCode'] = '02';
                    $vnpayResponse['Message'] = 'Order already confirmed';
                } else if ($responseCode == '00') {
                    if ($ipn) {
                        $order->status = 2;
                        $order->save();
                        $this->resetSession($order);
                        Notifications::createItem(1, $order->customers_id);
                    }
                    $vnpayResponse['RspCode'] = '00';
                    $vnpayResponse['Message'] = 'Confirm Success';
                } else {
                    $order->status = 5;
                    $order->save();
                    $vnpayResponse['RspCode'] = '00';
                    $vnpayResponse['Message'] = 'Confirm Success';
                }
            } else {
                $vnpayResponse['RspCode'] = '97';
                $vnpayResponse['Message'] = 'Chu ky khong hop le';
            }
        } catch (\Exception $e) {
            $vnpayResponse['RspCode'] = '99';
            $vnpayResponse['Message'] = 'Unknow error';
        }
        if ($order && $vnpayResponse['RspCode'] != '02') {
            $dataPayment = array(
                'Amount' => $request->get('vnp_Amount'),
                'BankCode' => $request->get('vnp_BankCode'),
                'BankTranNo' => $request->get('vnp_BankTranNo'),
                'CardType' => $request->get('vnp_CardType'),
                'OrderInfo' => $request->get('vnp_OrderInfo'),
                'PayDate' => $request->get('vnp_PayDate'),
                'ResponseCode' => $vnpayResponse['RspCode'],
                'TmnCode' => $request->get('vnp_TmnCode'),
                'TransactionNo' => $request->get('vnp_TransactionNo'),
                'TxnRef' => $request->get('vnp_TxnRef'),
                'orders_id' => $order->id,
                'SecureHashType' => $request->get('vnp_SecureHashType'),
                'SecureHash' => $request->get('vnp_SecureHash'),
                'status' => $order->status,
                'message' => $vnpayResponse['Message'],
            );
            CurrentModel::updateOrCreate(['TxnRef' => $request->get('vnp_TxnRef'), 'orders_id' => $order->id], $dataPayment);
        }
        return $vnpayResponse;
    }

    public function callback(Request $request, $service)
    {
        \Log::info(print_r('callback', true));
        $orderId = $request->get('vnp_TxnRef');
        $payment = CurrentModel::where(['TxnRef' => $orderId])->first();
        session(['orderId' => $orderId]);
        session(['vnpayResponse' => $this->checkVNPayCallback($request)]);
        $order = session('order');
        if ($order) {
            $this->resetSession(session('order'));
        }

        if ($payment && $payment->status == 2 && $payment->ResponseCode == '00') {
            return redirect('dat-hang-thanh-cong');
        } else {
            return redirect('fails');
        }
    }

    public function save(Request $request, $service)
    {
        \Log::info(print_r('ipn', true));
        $vnpayResponse = $this->checkVNPayCallback($request, true);
        \Log::info(print_r($vnpayResponse, true));
        return response($vnpayResponse);
    }

    private function resetSession($order)
    {
        $customer = Customers::where(['id' => $order->customers_id])->with(['shipping', 'orders', 'wishlists'])->first();
        session()->put('user', $customer);
        session()->forget(config('nksoft.addCart'));
        session()->forget('orderPayment');
    }

    public function resVnpay()
    {
        $order = session('order');
        if (!$order) {
            return $this->responseError('404');
        }
        $orderId = session('orderId');
        $vnpayResponse = session('vnpayResponse');
        $vnpayResponse['TxnRef'] = $orderId;
        $data = [
            'order' => $order,
            'resVnpay' => CurrentModel::select(['status', 'message', 'ResponseCode', 'TxnRef'])->where(['TxnRef' => $orderId])->first(),
            'vnpayResponse' => $vnpayResponse,
        ];
        session()->forget('order');
        return $this->responseViewSuccess($data);
    }
}
