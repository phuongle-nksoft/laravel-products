<?php

namespace Nksoft\Products\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Nksoft\Products\Models\Orders;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Orders $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('web@ruounhapkhau.com')
            ->subject('Đơn đặt hàng mới')
            ->view('master::email.order')
            ->with(['order' => $this->order]);
    }
}
