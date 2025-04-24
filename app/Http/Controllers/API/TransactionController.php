<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Models\Payment;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Validator;
use DateTime;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    #[OA\Post(
        path: '/vm/transaction/new',
        summary: 'Create new transaction',
        description: "Create new transaction and waiting for payment",
        tags: ['Transaction'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'vending_machine_id',
                        type: 'number',
                        description: 'Identifier for the vending machine',
                        example: '1'
                    ),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        description: 'Details of the products',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: 'product_id',
                                    type: 'string',
                                    description: 'Identifier for the product. It can be combination of Alpah-numeric character',
                                    example: 'ABC1234def'
                                ),
                                new OA\Property(
                                    property: 'qty',
                                    type: 'number',
                                    description: 'Quantity of item',
                                    example: 10
                                ),
                                new OA\Property(
                                    property: 'unit_price',
                                    type: 'number',
                                    format: 'float',
                                    description: 'Price of the product',
                                    example: 1.50
                                ),
                                new OA\Property(
                                    property: 'total_price',
                                    type: 'number',
                                    format: 'float',
                                    description: 'Total price of the product',
                                    example: 15.0
                                )
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'total_price',
                        type: 'number',
                        format: 'float',
                        description: 'Total price',
                        example: 15.0
                    )
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
                        new OA\Property(property: 'success', type: 'boolean', description: 'Indicates if the transaction was successful', example: true),
                        new OA\Property(property: 'message', type: 'string', description: 'Transaction status message', example: 'Waiting for payment.'),
                        new OA\Property(property: 'transaction_id', type: 'string', description: 'Transaction ID', example: 1739893690674),
                        new OA\Property(property: 'qrcode', type: 'string', description: 'URL of the QR code for payment', example: 'https://example.com/qrcode')
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
    public function newTransaction(Request $request)
    {
        $transaction = Transaction::create([
            'code' => Transaction::generateTransactionCode(),
            'vending_machine_id' => $request->vending_machine_id,
            'total_price' => $request->total_price,
            'status' => 2
        ]);
        foreach ($request->items as $item) {
            $transactionItem = TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price']
            ]);
        }
        $qrcode_url = $this->generateQRCode($transaction->code);
        return response()->json([
            'success' => true,
            'message' => 'Waiting for payment.',
            'transaction_id' => $transaction->code,
            'qrcode' => $qrcode_url
        ]);
    }

    private function generateQRCode($transactionCode)
    {
        $transaction = Transaction::with('items')->where('code', $transactionCode)->firstOrFail();
        $transaction->id = $transaction->code;
        unset($transaction->code);
        $renderer = new GDLibRenderer(400);
        $writer = new Writer($renderer);
        $filename = $transactionCode . '.png';
        $writer->writeFile(json_encode($transaction), 'payment_qr/' . $filename);
        return asset('payment_qr/' . $filename);
    }

    #[OA\Post(
        path: '/m/transaction/pay',
        summary: 'Pay for a Transaction',
        description: "Pay for a transaction using cash, credit, or a mix of both.",
        tags: ['Transaction'],
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'transaction_id',
                        type: 'number',
                        description: 'Identifier for the transaction',
                        example: '1739893690674'
                    ),
                    new OA\Property(
                        property: 'payment_method',
                        type: 'number',
                        description: 'Payment method (0 for cash, 1 for credit, 2 for mix)',
                        example: '0'
                    )
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
                        new OA\Property(property: 'success', type: 'boolean', description: 'Indicates if the transaction was successful', example: true),
                        new OA\Property(property: 'message', type: 'string', description: 'Transaction status message', example: 'Payment Success.'),
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
    public function pay(Request $request)
    {
        $transaction = Transaction::with('items')->where('code', $request->transaction_id)->firstOrFail();
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'payment_method' => 'required|in:0,1,2'
        ]);

        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)->first();
        $cashPayment = 0;
        $creditPayment = 0;
        $errorMessage = '';

        if ($transaction->status != 2 && $transaction->status != 'Pending') {
            $errorMessage = 'Cannot make payment for this transaction.';
        } elseif ($this->expired($transaction->created_at)) {
            $errorMessage = 'Payment request expired.';
        } elseif ($request->payment_method == 0) {
            $cashPayment = $transaction->total_price;
            if ($customer->cash_balance < $transaction->total_price) {
                $errorMessage = 'Insufficient balance.';
            }
        } elseif ($request->payment_method == 1) {
            $creditPayment = $transaction->total_price;
            if ($customer->credit_balance < $transaction->total_price) {
                $errorMessage = 'Insufficient credit.';
            }
        } else {
            $cashPayment = ($customer->cash_split / 100) * $transaction->total_price;
            $creditPayment = ($customer->credit_split / 100) * $transaction->total_price;
            if ($customer->cash_balance < $cashPayment) {
                $errorMessage = 'Insufficient balance.';
            } elseif ($customer->credit_balance < $creditPayment) {
                $errorMessage = 'Insufficient credit.';
            }
        }

        if ($errorMessage) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'vending_machine_id' => $transaction->vending_machine_id,
                'transaction_id' => $transaction->code,
                'pay_timestamp' => time(),
            ]);
        }

        $payment = Payment::create([
            'transaction_id' => $transaction->id,
            'customer_id' => $customer->id,
            'cash_amount' => $cashPayment,
            'credit_amount' => $creditPayment,
            'payment_method' => $request->payment_method,
            'status' => 'completed'
        ]);


        if ($payment) {
            $transaction->status = 1;
            $transaction->save();

            $customer->cash_balance -= $cashPayment;
            $customer->credit_balance -= $creditPayment;
            session(['transaction-type' => 'item_purchase']);
            $customer->save();
        }

        $payment->wallet_history_id = session('wallet-id');
        $payment->save();

        session()->forget('wallet-id');

        $payload = [
            'success' => true,
            'message' => 'Payment success.',
            'vending_machine_id' => $transaction->vending_machine_id,
            'transaction_id' => $transaction->code,
            'pay_timestamp' => time(),
        ];

        // $response = Http::post('http://qa2022.weimi24.com:9280/v2022/payment-center-web/rmfood/pay-notify', $payload);
        $response = Http::post('https://micron.weimi24.com/v8/payment-center-web/rmfood/pay-notify', $payload);

        return response()->json($payload);
    }

    private function expired($transactionDate)
    {
        $createdAtDate = new DateTime($transactionDate);
        $now = new DateTime();
        $expiryDate = clone $createdAtDate;
        $expiryDate->modify('+1 day');
        return $now > $expiryDate;
    }

    #[OA\Get(
        path: '/vm/transaction/get/{id}',
        summary: 'Get detail transaction',
        description: "get detail transaction",
        tags: ['Transaction'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'transaction id',
                schema: new OA\Schema(
                    type: 'string',
                    example: '1739893690674'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Transaction retrieved successfully'),
                        new OA\Property(property: 'status', type: 'string', example: true),
                        new OA\Property(property: 'data', type: 'Array', example: []),
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
    public function get($id)
    {
        $transaction = Transaction::where('code', $id)->firstOrFail();
        $transaction->id = $transaction->code;
        unset($transaction->code);
        return response()->json([
            'success' => true,
            'message' => 'transaction retrieved successfully.',
            'data' => $transaction
        ]);
    }
}
