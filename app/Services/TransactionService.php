<?php

namespace App\Services;

use App\Enums\ApiErrorCode;
use App\Enums\TopupMethod;
use App\Http\Responses\API\ErrorResponse;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTopupTransaction(int $customerId, float $amount, TopupMethod $method, string $source = null)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::find($customerId);
            if (!$customer) {
                throw new \Exception('customer not found');
            }
            // make the base transaction data first


        } catch (\Exception $exception) {
            DB::rollBack();
            return new ErrorResponse(ApiErrorCode::TRANSACTION_ERROR, $exception->getMessage(), $exception->getCode());
        }
    }


}