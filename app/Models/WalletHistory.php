<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'cash', 'credit', 'note', 'type', 'admin_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function topups()
    {
        return $this->hasMany(Topup::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
