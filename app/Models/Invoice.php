<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','number','type','party_id','date','due_date','currency_id',
        'subtotal','tax','discounts','total','status','payment_type','paid_amount','created_by','meta'
    ];

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) $model->id = (string) Str::uuid();
        });
    }
}