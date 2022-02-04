<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{

    protected $fillable = [
        'id', 'purchase_id','purchase_unit_id', 'quantity', 'product_id', 'total', 'product_variant_id',
        'cost', 'TaxNet', 'discount', 'discount_method', 'tax_method',
    ];

    protected $casts = [
        'total' => 'double',
        'cost' => 'double',
        'TaxNet' => 'double',
        'discount' => 'double',
        'quantity' => 'double',
        'purchase_id' => 'integer',
        'purchase_unit_id' => 'integer',
        'product_id' => 'integer',
        'product_variant_id' => 'integer',
    ];

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

}
