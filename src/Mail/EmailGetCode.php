<?php

namespace Nksoft\Products\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Nksoft\Products\Models\Customers;

class EmailGetCode extends Mailable
{
    use Queueable, SerializesModels;
    private $customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Customers $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('web@ruounhapkhau.com')
            ->subject('Mã lấy lại mật khẩu')
            ->view('products::email.get-code')
            ->with(['customer' => $this->customer]);
    }
}
