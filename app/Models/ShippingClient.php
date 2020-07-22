<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingClient extends Model
{
    protected $table = "shipping_clients";
    protected $guarded = ['id'];




    /**
     * Local Scopes
     */
    public function scopeIsCustomerRequiredScan($query, $customer_id)
    {
        return $query->where('tpl_client_id', $customer_id)->where('require_scan_serial_number', 1);
    }


    /**
     * Mutators
     */
    public function getRequiredScanSerialAttribute()
    {
        $required = '<span class="text-primary">Required</span>';
        $not_required = '<span class="text-muted">Not Required</span>';

        return ( $this->require_scan_serial_number == true ) ? $required : $not_required;
    }

    public function getShippingMarkupWithPercentAttribute()
    {
        return $this->shipping_markup . ' %';
    }
}
