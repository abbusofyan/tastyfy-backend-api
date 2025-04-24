<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use App\Models\WalletHistory;

/**
 * @OA\Schema(
 *     schema="Customer",
 *     title="Customer",
 *     description="Customer model",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", readOnly=true),
 *         @OA\Property(property="user_id", type="integer"),
 *         @OA\Property(property="first_name", type="string"),
 *         @OA\Property(property="last_name", type="string"),
 *         @OA\Property(property="phone", type="string"),
 *         @OA\Property(property="gender", type="string"),
 *         @OA\Property(property="dob", type="string", format="date"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     }
 * )
 */
class Customer extends Model
{
    use HasFactory;
    use HasRoles;

    protected $guard_name = 'web';
    protected $guarded = ['id'];

    protected $with = ['user', 'roles.permissions'];

    protected $appends = ['role_name'];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($customer) {
            if ($customer->isDirty('cash_balance') || $customer->isDirty('credit_balance')) {
                $original_cash_balance = $customer->getOriginal('cash_balance');
                $original_credit_balance = $customer->getOriginal('credit_balance');

                $cash = $customer->cash_balance - $original_cash_balance;
                $credit = $customer->credit_balance - $original_credit_balance;

                $type = session('transaction-type') ?? 'item_purchase';
                $note = '';

                $walletHistory = WalletHistory::create([
                    'customer_id'   => $customer->id,
                    'cash'   => $cash,
                    'credit' => $credit,
                    'type' => $type,
                    'admin_id' => $type === 'manual_adjustment' ? auth()->user()->id : null,
                ]);

                // Clear the session after use
                session()->forget('transaction-type');
                session(['wallet-id' => $walletHistory->id]);
            }
        });
    }

    public static function generateCustomerId()
    {
        $latestCustomer = Customer::where('customer_id', 'like', 'CUST%')
            ->orderByRaw("CAST(SUBSTRING(customer_id, 5) AS UNSIGNED) DESC")
            ->first();

        if ($latestCustomer) {
            $latestId = (int) substr($latestCustomer->customer_id, 4); // Extract the numeric part
            $newId = $latestId + 1;
        } else {
            $newId = 1; // If no customers exist, start from 1
        }

        $newCustomerId = 'CUST' . str_pad($newId, 4, '0', STR_PAD_LEFT);
        // dd($newCustomerId);
        return $newCustomerId;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Cash::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(Credit::class);
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->morphToMany(CustomerRole::class, 'model', 'model_has_customer_roles');
    }

    public function getRoleNameAttribute()
    {
        return $this->roles->first()?->name;
    }

    public function walletHistories()
    {
        return $this->hasMany(WalletHistory::class);
    }
}
