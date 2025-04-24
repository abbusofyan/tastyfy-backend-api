<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vending_machine_id',
        'total_price',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function topupTransaction()
    {
        return $this->hasOne(TopupTransaction::class);
    }

    public function purchaseTransaction()
    {
        return $this->hasOne(PurchaseTransaction::class);
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn($value) => TransactionType::from($value)->label(),
        );
    }

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn($value) => TransactionStatus::from($value)->label(),
        );
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    protected function generateTransactionCode(): string
    {
        do {
            $code = time() . random_int(100, 999);
            $exists = $this->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
