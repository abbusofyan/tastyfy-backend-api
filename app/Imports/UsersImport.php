<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Twilio\Rest\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;


class UsersImport implements WithHeadingRow, ToCollection
{

    protected $role;
    protected $collection;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function collection(Collection $rows)
    {
        // dd($rows); // Add dd here to debug

        // Check for duplicates in the entire Excel file before processing
        $phones = $rows->pluck('phone')->filter()->map(function ($phone) {
            return '+65' . $phone;
        });
        $emails = $rows->pluck('email')->filter();


        // Check for duplicates within the Excel file
        $duplicatePhones = $phones->duplicates();
        $duplicateEmails = $emails->duplicates();

        // Check for duplicates in the database
        $existingPhones = User::whereIn('phone', $phones)->pluck('phone');
        $existingEmails = User::whereIn('email', $emails)->pluck('email');

        if ($duplicatePhones->isNotEmpty()) {
            throw ValidationException::withMessages([
                'phone' => 'Duplicate phone numbers found in Excel file: ' . $duplicatePhones->implode(', ')
            ]);
        }

        if ($duplicateEmails->isNotEmpty()) {
            throw ValidationException::withMessages([
                'email' => 'Duplicate emails found in Excel file: ' . $duplicateEmails->implode(', ')
            ]);
        }

        if ($existingPhones->isNotEmpty()) {
            throw ValidationException::withMessages([
                'phone' => 'Phone numbers already exist in database: ' . $existingPhones->implode(', ')
            ]);
        }

        if ($existingEmails->isNotEmpty()) {
            throw ValidationException::withMessages([
                'email' => 'Emails already exist in database: ' . $existingEmails->implode(', ')
            ]);
        }

        foreach ($rows as $row) {
            if (isset($row['name'])) {
                $validator = Validator::make($row->toArray(), [
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255|unique:users,email',
                    'phone' => 'required|unique:users,phone',
                    'credit' => 'nullable|numeric',
                    'cash' => 'nullable|numeric',
                    'cash_split' => 'nullable|decimal:0,2',
                    'credit_split' => 'nullable|decimal:0,2',
                ]);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    throw ValidationException::withMessages($errors->toArray());
                }

                $phoneNumber = '+65' . $row['phone'];
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $phoneNumber,
                    'password' => Hash::make('tastesasia123')
                ]);

                $customerId = Customer::generateCustomerId();

                $customer = Customer::create([
                    'user_id' => $user->id,
                    'customer_id' => $customerId,
                    'credit_balance' => $row['credit'],
                    'cash_balance' => $row['cash'],
                    'credit_split' => $row['credit_split'],
                    'cash_split' => $row['cash_split'],
                ]);

                $message = "Dear " . $user->name . ", Your Taste Asia account has been successfully created! Your unique login password : tastesasia123 . Simply enter your registered phone number and unique login password for your first log in through the Taste Asia Mobile App.";
                $account_sid = getenv("TWILIO_SID");
                $auth_token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_number = getenv("TWILIO_PHONE_NUMBER");
                $client = new Client($account_sid, $auth_token);
                $client->messages->create(
                    $user->phone,
                    ['from' => $twilio_number, 'body' => $message]
                );

                $customer->assignRole($this->role);
            }
        }
    }
}
