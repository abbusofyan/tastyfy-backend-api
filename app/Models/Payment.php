<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'status',
        'cash_amount',
        'credit_amount',
        'payment_method',
        'wallet_history_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function walletHistory()
    {
        return $this->belongsTo(WalletHistory::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
