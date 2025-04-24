<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'method',
        'amount',
        'status',
        'wallet_history_id',
    ];

    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function walletHistory()
    {
        return $this->belongsTo(WalletHistory::class);
    }
}
