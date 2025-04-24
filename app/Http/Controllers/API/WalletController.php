<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Topup;
use App\Helpers\HitPayHelper;
use App\Enums\TransactionType;
use OpenApi\Attributes as OA;
use App\Http\Responses\API\BaseResponse;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{

    protected $hitPay;

    public function __construct(HitPayHelper $hitPay)
    {
        $this->hitPay = $hitPay;
    }

    #[OA\Post(
        path: '/m/wallet/topup',
        summary: 'Topup Cash Balance',
        description: "Topup customer's cash balance with Hitpay Payment Gateway",
        tags: ['Wallet'],
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'amount',
                        type: 'number',
                        format: 'float',
                        description: 'Amount to top-up',
                        example: 50.0
                    ),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', description: 'Top-up successful'),
                        new OA\Property(property: 'status', type: 'string', description: 'success'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function initiateTopUp(Request $request)
    {
        $user = $request->user();
        $reference_number = 'REF_TOPUP_' . time() . '_' . $user->id;

        $data = [
            'email' => $user->email,
            'amount' => $request->amount,
            'currency' => 'SGD',
            'reference_number' => $reference_number,
            'redirect_url' => route('api.wallet.topupSuccess', $reference_number),
            'webhook' => route('api.wallet.topupCallback')
            // 'webhook' => 'https://c864-158-140-180-20.ngrok-free.app/api/wallet/topup/callback'
        ];

        $response = $this->hitPay->createPayment($data);
        if ($response) {
            return response()->json([
                'success' => true,
                'message' => 'Waiting for payment.',
                'data' => $response
            ]);
        } else {
            return ['error' => 'Cant make a payment'];
        }
    }

    public function handleCallback(Request $request)
    {
        $ref_number_arr = explode('_', $request->reference_number);
        $user_id = end($ref_number_arr);

        if ($request->status == 'completed') {
            $customer = Customer::where('user_id', $user_id)->first();

            $topup = Topup::create([
                'customer_id' => $customer->id,
                'amount' => $request->amount,
                'status' => 1
            ]);

            $customer->cash_balance += $request->amount;
            session(['transaction-type' => 'topup']);
            $customer->save();

            $topup->wallet_history_id = session('wallet-id');
            $topup->save();

            session()->forget('wallet-id');

            return 'payment complete';
        }
    }

    public function topupSuccess($ref_number)
    {
        $status = $_GET['status'];
        if ($status == 'completed') {
            $ref_number_arr = explode('_', $ref_number);
            $user_id = end($ref_number_arr);
            $user = User::with('customer.roles')->findOrFail($user_id);
            $user_data = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'customer_id' => $user->customer->customer_id,
                'customer_role' => $user->customer->roles[0]->name,
                'credit_balance' => $user->customer->credit_balance,
                'cash_balance' => $user->customer->cash_balance,
                'credit_split' => $user->customer->credit_split,
                'cash_split' => $user->customer->cash_split
            ];
            $response = response()->json([
                'success' => true,
                'message' => 'Customer details retrieved successfully.',
                'data' => $user_data
            ]);
        } else {
            $response = response()->json([
                'success' => false,
                'message' => 'Payment failed.',
                'status' => $status,
            ]);
        }
        return $response;
    }

    #[OA\Post(
        path: '/m/wallet/history/{type}',
        summary: 'Usage history of cash or credit',
        description: "Type of wallet history (cash or credit)",
        tags: ['Wallet'],
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'path',
                required: true,
                description: 'Type of wallet history (cash or credit)',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'Cash'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', description: 'Top-up successful'),
                        new OA\Property(property: 'status', type: 'string', description: 'success'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]

    public function history($type = 'Cash', Request $request)
    {
        $type = strtolower($type);

        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:cash,credit', // Assuming 2 for mixed payment method
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user_id = $request->user()->id;
        try {
            $customer = Customer::where('user_id', $user_id)
                ->whereHas('walletHistories', function ($query) use ($type) {
                    $query->where($type, '!=', 0);
                })
                ->with(['walletHistories' => function ($query) use ($type) {
                    $query->where($type, '!=', 0);
                }])
                ->first();
            if ($customer) {
                $data = $customer->walletHistories;
            } else {
                $data = [];
            }
            return new BaseResponse($data, 'Wallet history retrieved successfully.', 200);
        } catch (\Exception $e) {
            return new BaseResponse($e, 'Cant get wallet history.', 500);
        }
    }
}
