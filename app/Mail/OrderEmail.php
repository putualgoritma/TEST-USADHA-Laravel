<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Order;
use App\Customer;

class OrderEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;
    protected $agent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orders_id,$agents_id)
    {
        $order = Order::with('products')
        ->with('customers')
        ->get()->find($orders_id);
        $agent = Customer::find($agents_id);
        $this->order = $order;
        $this->agent = $agent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@usadhabhakti.com')
            ->view('email.order')
            ->with([
            'order' => $this->order,
            'agent' => $this->agent,
            ]);
    }
}
