<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShopifyErrorReport extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = env('SHOPIFY_ERROR');
        $subject = 'Error Shopify integration';
        $name = 'Spectrum Oversee';
        
       // $test=$this->data;
        //echo "come ".$test['sender'];die();
       // print_r($test);die();
        //dd($test);
        return $this->from($address, $name)
        ->cc($address, $name)
        ->bcc($address, $name)
        ->replyTo($address, $name)
        ->subject($subject)
        ->view('mail.error_shopify')->with('data',$this->data);
    }
}
