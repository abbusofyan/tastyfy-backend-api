<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CreditsImport;

class CustomerController extends Controller
{
    public function store(CustomerRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            //create a user first
            $customerUser = User::create([
                'email'    => $data['email'],
                'phone'   => $data['phone'],
                'password' => Hash::make($data['password']),
                'name'     => $data['name'],
            ]);

            $customer = $customerUser->customer()->create([
                'customer_id'    => $data['customer_id'],
                'credit_balance' => 0,
                'cash_balance'   => 0,
                'credit_split'   => 0,
                'cash_split'     => 0,
            ]);
            $customerRole = CustomerRole::whereName($data['role'])->first();

            if (!$customerRole) {
                throw new \Exception('role not found');
            }

            $customer->assignRole($customerRole);

            $message = "Dear " . $customerUser->name . ", Your Taste Asia account has been successfully created! Your unique login password : '" . $data['password'] . "' . Simply enter your registered phone number and unique login password for your first log in through the Taste Asia Mobile App.";
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_PHONE_NUMBER");
            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $customerUser->phone,
                ['from' => $twilio_number, 'body' => $message]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data'    => $customer,
            ], 201);
            //            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], $e->getCode());
        }
    }

    public function update(CustomerRequest $request)
    {
        // Fetch the customer to be updated
        $customer = Customer::findOrFail($request->id);
        $customerUser = $customer->user;

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $customerUser->update([
                'email' => $data['email'] ?? $customerUser->email,
                'name'  => $data['name'] ?? $customerUser->name,
                'phone' => $data['phone'] ?? $customerUser->phone,
            ]);

            if (isset($data['password'])) {
                $customerUser->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            $customer->update([
                'customer_id' => $data['customer_id'] ?? $customer->customer_id,
            ]);

            // if (isset($data['role'])) {
            //     $customerRole = CustomerRole::whereName($data['role'])->first();
            //     if (!$customerRole) {
            //         throw new \Exception('role not found');
            //     }
            //     $customer->syncRoles([$customerRole]);
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data'    => $customer,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e->getMessage()], $e->getCode());
        }
    }



    public function addCredit(Request $request)
    {
        $user = $request->user();
        if ($user->customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'amount'      => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $customer = Customer::where('customer_id', $request->customer_id)->first();

                    // If customer doesn't exist, we return an error message
                    if (!$customer) {
                        return $fail('Customer not found');
                    }

                    // If customer does not have permission
                    if (!$customer->roles->first()?->hasPermissionTo('have credit')) { // optional chain
                        return $fail('You do not have permission to create a credit');
                    }

                    $currentCredit = $customer->credit_balance;

                    // Check if the resulting credit would be negative
                    if (($currentCredit + $value) < 0) {
                        return $fail('Credit balance cannot go below zero');
                    }
                },
            ],
        ]);

        $customer = Customer::where('customer_id', $validatedData['customer_id'])->first();

        $customer->credit_balance += $validatedData['amount'];
        session(['transaction-type' => 'manual_adjustment']);
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Credit added successfully',
            'data'    => $customer,
        ], 200);
    }

    public function splitCredit(Request $request)
    {
        $user = $request->user();
        if ($user->customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',

            ], 403);
        }

        Validator::make($request->all(), [
            'customer_id'  => ['required', 'exists:customers,customer_id'],
            'credit_split' => ['required', 'numeric', 'min:0', 'max:100'],
            'cash_split'   => ['required', 'numeric', 'min:0'],
        ])->after(function ($validator) use ($request) {
            $creditSplit = $request->input('credit_split');
            $cashSplit = $request->input('cash_split');

            // Check if the total split percentage is within the tolerance
            if (abs(($creditSplit + $cashSplit) - 100) >= 0.01) {
                $validator->errors()->add(
                    'cash_split',
                    'The credit and cash split percentages must add up to 100%'
                );
            }
        })->validate();

        $customer = Customer::where('customer_id', $request->customer_id)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $customer->update([
            'credit_split' => $request->credit_split,
            'cash_split'   => $request->cash_split,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Credit Split updated successfully',
            'data'    => $customer,
        ], 200);
    }
    public function importCredit(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $type = match ($extension) {
                'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
                'xls' => \Maatwebsite\Excel\Excel::XLS,
                'csv' => \Maatwebsite\Excel\Excel::CSV,
                default => \Maatwebsite\Excel\Excel::XLSX
            };

            $import = new CreditsImport;
            Excel::import($import, $file, null, $type);

            $response = [
                'message' => "Successfully processed {$import->getProcessedCount()} records",
                'errors' => $import->getErrors()
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing file: ' . $e->getMessage(),
                'errors' => []
            ], 500);
        }
    }
}
